/**
 * Admin JavaScript for Airtable Sync plugin
 */

(function($) {
	'use strict';

	let mappingIndex = 0;
	let fieldMappingIndices = {}; // Track field mapping indices per table mapping

	/**
	 * Initialize the admin functionality
	 */
	function init() {
		// Set initial mapping index based on existing rows
		mappingIndex = $('.airtable-sync-mapping-row').length;

		// Initialize field mapping indices for existing mappings
		$('.airtable-sync-mapping-row').each(function() {
			const index = $(this).data('index');
			const fieldCount = $(this).find('.field-mapping-row').length;
			fieldMappingIndices[index] = fieldCount;
		});

		// Load bases button
		$('#airtable_sync_load_bases').on('click', loadBases);

		// Add mapping button
		$('#airtable_sync_add_mapping').on('click', addMapping);

		// Remove mapping buttons (delegated for dynamic elements)
		$(document).on('click', '.remove-mapping', removeMapping);

		// Configure fields button
		$(document).on('click', '.configure-fields', toggleFieldMapper);

		// Load fields button
		$(document).on('click', '.load-fields', loadFields);

		// Remove field mapping button
		$(document).on('click', '.remove-field-mapping', removeFieldMapping);

		// Monitor API key changes to enable/disable base selector
		$('#airtable_sync_api_key').on('input', function() {
			const hasApiKey = $(this).val().trim() !== '';
			$('#airtable_sync_base_id').prop('disabled', !hasApiKey);
			$('#airtable_sync_load_bases').prop('disabled', !hasApiKey);
		});

		// Monitor base selection to enable/disable add mapping button
		$('#airtable_sync_base_id').on('change', function() {
			const hasBase = $(this).val() !== '';
			$('#airtable_sync_add_mapping').prop('disabled', !hasBase);
		});

		// Load tables when a new mapping row is added
		$(document).on('focus', '.airtable-table-select', function() {
			const $select = $(this);
			if ($select.find('option').length <= 1) {
				loadTablesForSelect($select);
			}
		});

		// Update hidden table name field and enable configure button when table is selected
		$(document).on('change', '.airtable-table-select', function() {
			const $select = $(this);
			const $row = $select.closest('.airtable-sync-mapping-row');
			const $hiddenField = $row.find('.table-name-hidden');
			const $viewSelect = $row.find('.airtable-view-select');
			const $postTypeSelect = $row.find('.post-type-select');
			const $configureButton = $row.find('.configure-fields');
			const selectedText = $select.find('option:selected').text();

			if ($select.val()) {
				$hiddenField.val(selectedText);
				// Enable view selector and load views
				$viewSelect.prop('disabled', false);
				loadViewsForSelect($viewSelect, $select.val());
			} else {
				$hiddenField.val('');
				// Disable and reset view selector
				$viewSelect.prop('disabled', true).empty().append('<option value="">All records (default view)</option>');
			}

			// Enable configure button if both table and post type are selected
			const hasTable = $select.val() !== '';
			const hasPostType = $postTypeSelect.val() !== '';
			$configureButton.prop('disabled', !(hasTable && hasPostType));
		});

		// Update hidden view name when view is selected
		$(document).on('change', '.airtable-view-select', function() {
			const $select = $(this);
			const $row = $select.closest('.airtable-sync-mapping-row');
			const $hiddenField = $row.find('.view-name-hidden');
			const selectedText = $select.find('option:selected').text();

			if ($select.val()) {
				$hiddenField.val(selectedText);
			} else {
				$hiddenField.val('');
			}
		});

		// Enable configure button when post type is selected
		$(document).on('change', '.post-type-select', function() {
			const $select = $(this);
			const $row = $select.closest('.airtable-sync-mapping-row');
			const $tableSelect = $row.find('.airtable-table-select');
			const $configureButton = $row.find('.configure-fields');

			const hasTable = $tableSelect.val() !== '';
			const hasPostType = $select.val() !== '';
			$configureButton.prop('disabled', !(hasTable && hasPostType));
		});

		// Handle destination type change
		$(document).on('change', '.destination-type-select', function() {
			const $select = $(this);
			const $fieldRow = $select.closest('.field-mapping-row');
			const $destinationSelect = $fieldRow.find('.destination-field-select');
			const $mappingRow = $select.closest('.airtable-sync-mapping-row');
			const mappingIndex = $mappingRow.data('index');
			const postType = $mappingRow.find('.post-type-select').val();

			if ($select.val() && postType) {
				loadDestinationFields($destinationSelect, $select.val(), postType, mappingIndex);
			} else {
				$destinationSelect.empty().append('<option value="">Select destination...</option>');
			}
		});

		// Update hidden destination name when destination is selected
		$(document).on('change', '.destination-field-select', function() {
			const $select = $(this);
			const $fieldRow = $select.closest('.field-mapping-row');
			const $hiddenField = $fieldRow.find('.destination-name-hidden');
			const selectedText = $select.find('option:selected').text();

			if ($select.val()) {
				$hiddenField.val(selectedText);
			} else {
				$hiddenField.val('');
			}
		});
	}

	/**
	 * Load Airtable bases
	 */
	function loadBases() {
		const $button = $('#airtable_sync_load_bases');
		const $select = $('#airtable_sync_base_id');
		const $loading = $('#airtable_sync_base_loading');
		const apiKey = $('#airtable_sync_api_key').val();

		if (!apiKey) {
			alert('Please enter an API key first.');
			return;
		}

		// Show loading state
		$button.prop('disabled', true);
		$loading.show();

		$.ajax({
			url: airtableSyncAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'airtable_sync_get_bases',
				nonce: airtableSyncAdmin.nonce,
				api_key: apiKey
			},
			success: function(response) {
				if (response.success && response.data.bases) {
					// Clear existing options except the first
					$select.find('option:not(:first)').remove();

					// Add bases to select
					response.data.bases.forEach(function(base) {
						$select.append(
							$('<option></option>')
								.attr('value', base.id)
								.text(base.name)
						);
					});

					showMessage('Bases loaded successfully!', 'success');
				} else {
					const errorMsg = response.data && response.data.message
						? response.data.message
						: 'Failed to load bases.';
					showMessage(errorMsg, 'error');
				}
			},
			error: function(xhr, status, error) {
				showMessage('Error loading bases: ' + error, 'error');
			},
			complete: function() {
				$button.prop('disabled', false);
				$loading.hide();
			}
		});
	}

	/**
	 * Load tables for a specific select element
	 */
	function loadTablesForSelect($select) {
		const apiKey = $('#airtable_sync_api_key').val();
		const baseId = $('#airtable_sync_base_id').val();

		if (!apiKey || !baseId) {
			return;
		}

		// Add loading option
		$select.prop('disabled', true);
		$select.find('option:first').text('Loading tables...');

		$.ajax({
			url: airtableSyncAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'airtable_sync_get_tables',
				nonce: airtableSyncAdmin.nonce,
				api_key: apiKey,
				base_id: baseId
			},
			success: function(response) {
				if (response.success && response.data.tables) {
					// Clear existing options
					$select.empty();
					$select.append('<option value="">Select table...</option>');

					// Add tables to select
					response.data.tables.forEach(function(table) {
						$select.append(
							$('<option></option>')
								.attr('value', table.id)
								.text(table.name)
						);
					});
				} else {
					const errorMsg = response.data && response.data.message
						? response.data.message
						: 'Failed to load tables.';
					$select.find('option:first').text('Error loading tables');
					showMessage(errorMsg, 'error');
				}
			},
			error: function(xhr, status, error) {
				$select.find('option:first').text('Error loading tables');
				showMessage('Error loading tables: ' + error, 'error');
			},
			complete: function() {
				$select.prop('disabled', false);
			}
		});
	}

	/**
	 * Load views for a specific select element
	 */
	function loadViewsForSelect($select, tableId) {
		const apiKey = $('#airtable_sync_api_key').val();
		const baseId = $('#airtable_sync_base_id').val();

		if (!apiKey || !baseId || !tableId) {
			return;
		}

		// Add loading option
		const currentValue = $select.val();
		$select.prop('disabled', true);
		$select.empty().append('<option value="">Loading views...</option>');

		$.ajax({
			url: airtableSyncAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'airtable_sync_get_views',
				nonce: airtableSyncAdmin.nonce,
				api_key: apiKey,
				base_id: baseId,
				table_id: tableId
			},
			success: function(response) {
				if (response.success && response.data.views) {
					// Clear and add default option
					$select.empty();
					$select.append('<option value="">All records (default view)</option>');

					// Add views to select
					response.data.views.forEach(function(view) {
						$select.append(
							$('<option></option>')
								.attr('value', view.id)
								.text(view.name)
						);
					});

					// Restore previous selection if it exists
					if (currentValue) {
						$select.val(currentValue);
					}
				} else {
					const errorMsg = response.data && response.data.message
						? response.data.message
						: 'Failed to load views.';
					$select.empty().append('<option value="">All records (default view)</option>');
					showMessage(errorMsg, 'error');
				}
			},
			error: function(xhr, status, error) {
				$select.empty().append('<option value="">All records (default view)</option>');
				showMessage('Error loading views: ' + error, 'error');
			},
			complete: function() {
				$select.prop('disabled', false);
			}
		});
	}

	/**
	 * Add a new mapping row
	 */
	function addMapping(e) {
		e.preventDefault();

		const template = $('#airtable-sync-mapping-template').html();
		const newRow = template.replace(/{{INDEX}}/g, mappingIndex);

		$('#airtable_sync_mappings_container').append(newRow);
		fieldMappingIndices[mappingIndex] = 0;
		mappingIndex++;
	}

	/**
	 * Remove a mapping row
	 */
	function removeMapping(e) {
		e.preventDefault();

		if (confirm('Are you sure you want to remove this mapping?')) {
			const $row = $(this).closest('.airtable-sync-mapping-row');
			const index = $row.data('index');
			delete fieldMappingIndices[index];
			$row.remove();
		}
	}

	/**
	 * Toggle field mapper visibility
	 */
	function toggleFieldMapper(e) {
		e.preventDefault();

		const $button = $(this);
		const $row = $button.closest('.airtable-sync-mapping-row');
		const $container = $row.find('.field-mappings-container');

		$container.slideToggle(300);
		$button.text($container.is(':visible') ? 'Hide Fields' : 'Configure Fields');
	}

	/**
	 * Load fields for mapping
	 */
	function loadFields(e) {
		e.preventDefault();

		const $button = $(this);
		const mappingIndex = $button.data('index');
		const $row = $('.airtable-sync-mapping-row[data-index="' + mappingIndex + '"]');
		const $list = $row.find('.field-mappings-list[data-index="' + mappingIndex + '"]');
		const $empty = $row.find('.field-mappings-empty');

		const apiKey = $('#airtable_sync_api_key').val();
		const baseId = $('#airtable_sync_base_id').val();
		const tableId = $row.find('.airtable-table-select').val();
		const viewId = $row.find('.airtable-view-select').val();
		const postType = $row.find('.post-type-select').val();

		if (!apiKey || !baseId || !tableId || !postType) {
			alert('Please configure table and post type mapping first.');
			return;
		}

		$button.prop('disabled', true).text('Loading...');

		// Load both Airtable fields and WordPress fields
		Promise.all([
			loadAirtableFields(apiKey, baseId, tableId, viewId),
			loadWordPressFields(postType)
		]).then(function(results) {
			const airtableFields = results[0];
			const wpFields = results[1];

			// Clear existing field mappings
			$list.empty();

			// Create field mapping rows with smart suggestions
			if (airtableFields && airtableFields.length > 0) {
				airtableFields.forEach(function(field, index) {
					const suggestion = suggestMapping(field, wpFields);
					const fieldRow = createFieldMappingRow(mappingIndex, index, field, suggestion, wpFields);
					$list.append(fieldRow);
				});

				// Update field mapping index
				fieldMappingIndices[mappingIndex] = airtableFields.length;

				$empty.hide();
				showMessage('Fields loaded successfully! Review suggested mappings below.', 'success');
			} else {
				$empty.show();
				showMessage('No fields found in the selected table.', 'error');
			}
		}).catch(function(error) {
			showMessage('Error loading fields: ' + error, 'error');
		}).finally(function() {
			$button.prop('disabled', false).text('Reload Fields');
		});
	}

	/**
	 * Load Airtable fields
	 */
	function loadAirtableFields(apiKey, baseId, tableId, viewId) {
		return new Promise(function(resolve, reject) {
			const requestData = {
				action: 'airtable_sync_get_table_schema',
				nonce: airtableSyncAdmin.nonce,
				api_key: apiKey,
				base_id: baseId,
				table_id: tableId
			};

			// Add view_id if provided
			if (viewId) {
				requestData.view_id = viewId;
			}

			$.ajax({
				url: airtableSyncAdmin.ajaxUrl,
				type: 'POST',
				data: requestData,
				success: function(response) {
					if (response.success && response.data.fields) {
						resolve(response.data.fields);
					} else {
						reject(response.data && response.data.message ? response.data.message : 'Failed to load fields');
					}
				},
				error: function(xhr, status, error) {
					reject(error);
				}
			});
		});
	}

	/**
	 * Load WordPress fields
	 */
	function loadWordPressFields(postType) {
		return new Promise(function(resolve, reject) {
			$.ajax({
				url: airtableSyncAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'airtable_sync_get_wp_fields',
					nonce: airtableSyncAdmin.nonce,
					post_type: postType
				},
				success: function(response) {
					if (response.success) {
						resolve(response.data);
					} else {
						reject(response.data && response.data.message ? response.data.message : 'Failed to load fields');
					}
				},
				error: function(xhr, status, error) {
					reject(error);
				}
			});
		});
	}

	/**
	 * Suggest a mapping for an Airtable field
	 */
	function suggestMapping(airtableField, wpFields) {
		const fieldName = airtableField.name.toLowerCase();
		const fieldType = airtableField.type;

		// Primary field usually maps to title
		if (fieldName.includes('name') || fieldName.includes('title')) {
			return { type: 'core', key: 'post_title', name: 'Post Title' };
		}

		// Description/content fields
		if (fieldName.includes('description') || fieldName.includes('content') || fieldName.includes('body')) {
			return { type: 'core', key: 'post_content', name: 'Post Content' };
		}

		// Date fields
		if (fieldType === 'date' || fieldType === 'dateTime') {
			if (fieldName.includes('publish') || fieldName.includes('date')) {
				return { type: 'core', key: 'post_date', name: 'Post Date' };
			}
		}

		// Taxonomy suggestions
		if (fieldType === 'singleSelect' || fieldType === 'multipleSelects') {
			// Check for common taxonomy names
			if (fieldName.includes('category') || fieldName.includes('categories')) {
				const catTax = wpFields.taxonomies.find(t => t.key === 'category');
				if (catTax) {
					return { type: 'taxonomy', key: catTax.key, name: catTax.name };
				}
			}
			if (fieldName.includes('tag') || fieldName.includes('tags')) {
				const tagTax = wpFields.taxonomies.find(t => t.key === 'post_tag');
				if (tagTax) {
					return { type: 'taxonomy', key: tagTax.key, name: tagTax.name };
				}
			}

			// Try to match by similar name
			const matchingTax = wpFields.taxonomies.find(function(tax) {
				return fieldName.includes(tax.key.toLowerCase()) ||
					   tax.name.toLowerCase().includes(fieldName);
			});
			if (matchingTax) {
				return { type: 'taxonomy', key: matchingTax.key, name: matchingTax.name };
			}
		}

		// Try to match ACF fields by name
		if (wpFields.acf && wpFields.acf.length > 0) {
			const matchingAcf = wpFields.acf.find(function(acfField) {
				return acfField.field_name.toLowerCase() === fieldName ||
					   acfField.name.toLowerCase() === fieldName;
			});
			if (matchingAcf) {
				return { type: 'acf', key: matchingAcf.key, name: matchingAcf.name };
			}
		}

		return null;
	}

	/**
	 * Create a field mapping row
	 */
	function createFieldMappingRow(mappingIndex, fieldIndex, airtableField, suggestion, wpFields) {
		const optionName = 'airtable_sync_settings[table_mappings][' + mappingIndex + '][field_mappings][' + fieldIndex + ']';

		const $row = $('<div></div>')
			.addClass('field-mapping-row')
			.attr('data-field-index', fieldIndex);

		// Airtable field info
		const $airtableDiv = $('<div></div>')
			.addClass('field-mapping-airtable')
			.html(
				'<strong>' + escapeHtml(airtableField.name) + '</strong>' +
				'<span class="field-type">(' + escapeHtml(airtableField.type) + ')</span>' +
				'<input type="hidden" name="' + optionName + '[airtable_field_id]" value="' + escapeHtml(airtableField.id) + '" />' +
				'<input type="hidden" name="' + optionName + '[airtable_field_name]" value="' + escapeHtml(airtableField.name) + '" />' +
				'<input type="hidden" name="' + optionName + '[airtable_field_type]" value="' + escapeHtml(airtableField.type) + '" />'
			);

		const $arrow = $('<span></span>')
			.addClass('field-mapping-arrow')
			.text('→');

		// Destination fields
		const $destinationDiv = $('<div></div>').addClass('field-mapping-destination');

		// Destination type select
		const $typeSelect = $('<select></select>')
			.addClass('destination-type-select')
			.attr('name', optionName + '[destination_type]')
			.append('<option value="">Select type...</option>')
			.append('<option value="core">Core WordPress</option>')
			.append('<option value="taxonomy">Taxonomy</option>')
			.append('<option value="acf">ACF Field</option>');

		// Destination field select
		const $fieldSelect = $('<select></select>')
			.addClass('destination-field-select')
			.attr('name', optionName + '[destination_key]')
			.append('<option value="">Select destination...</option>');

		const $hiddenName = $('<input type="hidden" />')
			.addClass('destination-name-hidden')
			.attr('name', optionName + '[destination_name]')
			.val('');

		// Apply suggestion if available
		if (suggestion) {
			$typeSelect.val(suggestion.type);
			populateDestinationSelect($fieldSelect, suggestion.type, wpFields);
			$fieldSelect.val(suggestion.key);
			$hiddenName.val(suggestion.name);
		}

		$destinationDiv.append($typeSelect).append($fieldSelect).append($hiddenName);

		// Remove button
		const $removeButton = $('<button></button>')
			.addClass('button button-small remove-field-mapping')
			.attr('type', 'button')
			.text('×');

		$row.append($airtableDiv).append($arrow).append($destinationDiv).append($removeButton);

		return $row;
	}

	/**
	 * Populate destination select based on type
	 */
	function populateDestinationSelect($select, type, wpFields) {
		$select.empty().append('<option value="">Select destination...</option>');

		if (type === 'core') {
			wpFields.core.forEach(function(field) {
				$select.append(
					$('<option></option>')
						.val(field.key)
						.text(field.name)
				);
			});
		} else if (type === 'taxonomy') {
			wpFields.taxonomies.forEach(function(tax) {
				$select.append(
					$('<option></option>')
						.val(tax.key)
						.text(tax.name)
				);
			});
		} else if (type === 'acf' && wpFields.acf) {
			// Group by field group
			const groups = {};
			wpFields.acf.forEach(function(field) {
				if (!groups[field.group]) {
					groups[field.group] = [];
				}
				groups[field.group].push(field);
			});

			Object.keys(groups).forEach(function(groupName) {
				const $optgroup = $('<optgroup></optgroup>').attr('label', groupName);
				groups[groupName].forEach(function(field) {
					$optgroup.append(
						$('<option></option>')
							.val(field.key)
							.text(field.name)
					);
				});
				$select.append($optgroup);
			});
		}
	}

	/**
	 * Load destination fields
	 */
	function loadDestinationFields($select, destinationType, postType, mappingIndex) {
		$select.prop('disabled', true);

		loadWordPressFields(postType).then(function(wpFields) {
			populateDestinationSelect($select, destinationType, wpFields);
			$select.prop('disabled', false);
		}).catch(function(error) {
			showMessage('Error loading destination fields: ' + error, 'error');
			$select.prop('disabled', false);
		});
	}

	/**
	 * Remove a field mapping row
	 */
	function removeFieldMapping(e) {
		e.preventDefault();
		$(this).closest('.field-mapping-row').remove();
	}

	/**
	 * Show a temporary message
	 */
	function showMessage(message, type) {
		const className = type === 'success' ? 'airtable-sync-success' : 'airtable-sync-error';
		const $message = $('<div></div>')
			.addClass(className)
			.text(message)
			.insertAfter('#airtable_sync_load_bases');

		setTimeout(function() {
			$message.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}

	/**
	 * Escape HTML
	 */
	function escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	// Initialize when document is ready
	$(document).ready(init);

})(jQuery);
