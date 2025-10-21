<?php
/**
 * Admin functionality for Airtable Sync
 *
 * @package Airtable_Sync
 */

class Airtable_Sync_Admin {

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	private $option_name = 'airtable_sync_settings';

	/**
	 * Initialize the admin functionality.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_airtable_sync_get_bases', array( $this, 'ajax_get_bases' ) );
		add_action( 'wp_ajax_airtable_sync_get_tables', array( $this, 'ajax_get_tables' ) );
		add_action( 'wp_ajax_airtable_sync_get_views', array( $this, 'ajax_get_views' ) );
		add_action( 'wp_ajax_airtable_sync_get_table_schema', array( $this, 'ajax_get_table_schema' ) );
		add_action( 'wp_ajax_airtable_sync_get_wp_fields', array( $this, 'ajax_get_wp_fields' ) );
	}

	/**
	 * Add admin menu page.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Airtable Sync', 'airtable-sync' ),
			__( 'Airtable Sync', 'airtable-sync' ),
			'manage_options',
			'airtable-sync',
			array( $this, 'render_settings_page' ),
			'dashicons-update',
			30
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'airtable_sync_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		// API Key Section
		add_settings_section(
			'airtable_sync_api_section',
			__( 'API Configuration', 'airtable-sync' ),
			array( $this, 'render_api_section' ),
			'airtable-sync'
		);

		add_settings_field(
			'api_key',
			__( 'Airtable API Key', 'airtable-sync' ),
			array( $this, 'render_api_key_field' ),
			'airtable-sync',
			'airtable_sync_api_section'
		);

		add_settings_field(
			'base_id',
			__( 'Airtable Base', 'airtable-sync' ),
			array( $this, 'render_base_selector_field' ),
			'airtable-sync',
			'airtable_sync_api_section'
		);

		// Table Mappings Section
		add_settings_section(
			'airtable_sync_mappings_section',
			__( 'Table to Post Type Mappings', 'airtable-sync' ),
			array( $this, 'render_mappings_section' ),
			'airtable-sync'
		);

		add_settings_field(
			'table_mappings',
			__( 'Mappings', 'airtable-sync' ),
			array( $this, 'render_table_mappings_field' ),
			'airtable-sync',
			'airtable_sync_mappings_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input The input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['api_key'] ) ) {
			$sanitized['api_key'] = sanitize_text_field( $input['api_key'] );
		}

		if ( isset( $input['base_id'] ) ) {
			$sanitized['base_id'] = sanitize_text_field( $input['base_id'] );
		}

		if ( isset( $input['table_mappings'] ) && is_array( $input['table_mappings'] ) ) {
			$sanitized['table_mappings'] = array();
			foreach ( $input['table_mappings'] as $mapping ) {
				if ( ! empty( $mapping['table_id'] ) && ! empty( $mapping['post_type'] ) ) {
					$sanitized_mapping = array(
						'table_id' => sanitize_text_field( $mapping['table_id'] ),
						'table_name' => sanitize_text_field( $mapping['table_name'] ),
						'post_type' => sanitize_text_field( $mapping['post_type'] ),
						'view_id' => isset( $mapping['view_id'] ) ? sanitize_text_field( $mapping['view_id'] ) : '',
						'view_name' => isset( $mapping['view_name'] ) ? sanitize_text_field( $mapping['view_name'] ) : '',
					);

					// Sanitize field mappings if present
					if ( isset( $mapping['field_mappings'] ) && is_array( $mapping['field_mappings'] ) ) {
						$sanitized_mapping['field_mappings'] = array();
						foreach ( $mapping['field_mappings'] as $field_mapping ) {
							if ( ! empty( $field_mapping['airtable_field_id'] ) && ! empty( $field_mapping['destination_type'] ) ) {
								$sanitized_mapping['field_mappings'][] = array(
									'airtable_field_id' => sanitize_text_field( $field_mapping['airtable_field_id'] ),
									'airtable_field_name' => sanitize_text_field( $field_mapping['airtable_field_name'] ),
									'airtable_field_type' => sanitize_text_field( $field_mapping['airtable_field_type'] ),
									'destination_type' => sanitize_text_field( $field_mapping['destination_type'] ),
									'destination_key' => sanitize_text_field( $field_mapping['destination_key'] ),
									'destination_name' => sanitize_text_field( $field_mapping['destination_name'] ),
								);
							}
						}
					}

					$sanitized['table_mappings'][] = $sanitized_mapping;
				}
			}
		}

		return $sanitized;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_airtable-sync' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'airtable-sync-admin',
			AIRTABLE_SYNC_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			AIRTABLE_SYNC_VERSION
		);

		wp_enqueue_script(
			'airtable-sync-admin',
			AIRTABLE_SYNC_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			AIRTABLE_SYNC_VERSION,
			true
		);

		wp_localize_script(
			'airtable-sync-admin',
			'airtableSyncAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'airtable_sync_nonce' ),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show success message if settings were saved
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error(
				'airtable_sync_messages',
				'airtable_sync_message',
				__( 'Settings saved successfully.', 'airtable-sync' ),
				'success'
			);
		}

		settings_errors( 'airtable_sync_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'airtable_sync_settings_group' );
				do_settings_sections( 'airtable-sync' );
				submit_button( __( 'Save Settings', 'airtable-sync' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render API section description.
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Configure your Airtable API credentials and select the base you want to sync.', 'airtable-sync' ) . '</p>';
	}

	/**
	 * Render API key field.
	 */
	public function render_api_key_field() {
		$settings = get_option( $this->option_name, array() );
		$api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		?>
		<input
			type="password"
			id="airtable_sync_api_key"
			name="<?php echo esc_attr( $this->option_name ); ?>[api_key]"
			value="<?php echo esc_attr( $api_key ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %s: URL to Airtable API documentation */
				esc_html__( 'Enter your Airtable API key. You can find it in your %s.', 'airtable-sync' ),
				'<a href="https://airtable.com/create/tokens" target="_blank">' . esc_html__( 'Airtable account settings', 'airtable-sync' ) . '</a>'
			);
			?>
		</p>
		<?php
	}

	/**
	 * Render base selector field.
	 */
	public function render_base_selector_field() {
		$settings = get_option( $this->option_name, array() );
		$base_id = isset( $settings['base_id'] ) ? $settings['base_id'] : '';
		$api_key = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		?>
		<select
			id="airtable_sync_base_id"
			name="<?php echo esc_attr( $this->option_name ); ?>[base_id]"
			class="regular-text"
			<?php echo empty( $api_key ) ? 'disabled' : ''; ?>
		>
			<option value=""><?php esc_html_e( 'Select a base...', 'airtable-sync' ); ?></option>
			<?php if ( ! empty( $base_id ) ) : ?>
				<option value="<?php echo esc_attr( $base_id ); ?>" selected><?php echo esc_html( $base_id ); ?></option>
			<?php endif; ?>
		</select>
		<button type="button" id="airtable_sync_load_bases" class="button" <?php echo empty( $api_key ) ? 'disabled' : ''; ?>>
			<?php esc_html_e( 'Load Bases', 'airtable-sync' ); ?>
		</button>
		<p class="description">
			<?php esc_html_e( 'Select the Airtable base you want to sync with WordPress.', 'airtable-sync' ); ?>
		</p>
		<div id="airtable_sync_base_loading" style="display:none;">
			<span class="spinner is-active"></span>
		</div>
		<?php
	}

	/**
	 * Render mappings section description.
	 */
	public function render_mappings_section() {
		echo '<p>' . esc_html__( 'Map Airtable tables to WordPress post types.', 'airtable-sync' ) . '</p>';
	}

	/**
	 * Render table mappings field.
	 */
	public function render_table_mappings_field() {
		$settings = get_option( $this->option_name, array() );
		$mappings = isset( $settings['table_mappings'] ) ? $settings['table_mappings'] : array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$base_id = isset( $settings['base_id'] ) ? $settings['base_id'] : '';
		?>
		<div id="airtable_sync_table_mappings">
			<div id="airtable_sync_mappings_container">
				<?php if ( ! empty( $mappings ) ) : ?>
					<?php foreach ( $mappings as $index => $mapping ) : ?>
						<div class="airtable-sync-mapping-row" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="mapping-header">
								<div class="mapping-selects">
									<div class="mapping-select-group">
										<label><?php esc_html_e( 'Airtable Table', 'airtable-sync' ); ?></label>
										<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][table_id]" class="airtable-table-select" data-index="<?php echo esc_attr( $index ); ?>">
											<option value=""><?php esc_html_e( 'Select table...', 'airtable-sync' ); ?></option>
											<option value="<?php echo esc_attr( $mapping['table_id'] ); ?>" selected>
												<?php echo esc_html( isset( $mapping['table_name'] ) ? $mapping['table_name'] : $mapping['table_id'] ); ?>
											</option>
										</select>
										<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][table_name]" value="<?php echo esc_attr( isset( $mapping['table_name'] ) ? $mapping['table_name'] : '' ); ?>" class="table-name-hidden" />
									</div>
									<div class="mapping-select-group">
										<label><?php esc_html_e( 'View (Optional)', 'airtable-sync' ); ?></label>
										<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][view_id]" class="airtable-view-select" data-index="<?php echo esc_attr( $index ); ?>" <?php echo empty( $mapping['table_id'] ) ? 'disabled' : ''; ?>>
											<option value=""><?php esc_html_e( 'All records (default view)', 'airtable-sync' ); ?></option>
											<?php if ( ! empty( $mapping['view_id'] ) ) : ?>
												<option value="<?php echo esc_attr( $mapping['view_id'] ); ?>" selected>
													<?php echo esc_html( isset( $mapping['view_name'] ) ? $mapping['view_name'] : $mapping['view_id'] ); ?>
												</option>
											<?php endif; ?>
										</select>
										<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][view_name]" value="<?php echo esc_attr( isset( $mapping['view_name'] ) ? $mapping['view_name'] : '' ); ?>" class="view-name-hidden" />
									</div>
									<span class="mapping-arrow">→</span>
									<div class="mapping-select-group">
										<label><?php esc_html_e( 'Post Type', 'airtable-sync' ); ?></label>
										<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][post_type]" class="post-type-select" data-index="<?php echo esc_attr( $index ); ?>">
											<option value=""><?php esc_html_e( 'Select post type...', 'airtable-sync' ); ?></option>
											<?php foreach ( $post_types as $post_type ) : ?>
												<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $mapping['post_type'], $post_type->name ); ?>>
													<?php echo esc_html( $post_type->labels->name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="mapping-actions">
									<button type="button" class="button configure-fields" data-index="<?php echo esc_attr( $index ); ?>" <?php echo empty( $mapping['table_id'] ) || empty( $mapping['post_type'] ) ? 'disabled' : ''; ?>>
										<?php esc_html_e( 'Configure Fields', 'airtable-sync' ); ?>
									</button>
									<button type="button" class="button remove-mapping"><?php esc_html_e( 'Remove', 'airtable-sync' ); ?></button>
								</div>
							</div>
							<?php $this->render_field_mappings_section( $index, $mapping ); ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<button type="button" id="airtable_sync_add_mapping" class="button" <?php echo empty( $base_id ) ? 'disabled' : ''; ?>>
				<?php esc_html_e( 'Add Mapping', 'airtable-sync' ); ?>
			</button>
		</div>

		<script type="text/html" id="airtable-sync-mapping-template">
			<div class="airtable-sync-mapping-row" data-index="{{INDEX}}">
				<div class="mapping-header">
					<div class="mapping-selects">
						<div class="mapping-select-group">
							<label><?php esc_html_e( 'Airtable Table', 'airtable-sync' ); ?></label>
							<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][{{INDEX}}][table_id]" class="airtable-table-select" data-index="{{INDEX}}">
								<option value=""><?php esc_html_e( 'Select table...', 'airtable-sync' ); ?></option>
							</select>
							<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][{{INDEX}}][table_name]" value="" class="table-name-hidden" />
						</div>
						<div class="mapping-select-group">
							<label><?php esc_html_e( 'View (Optional)', 'airtable-sync' ); ?></label>
							<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][{{INDEX}}][view_id]" class="airtable-view-select" data-index="{{INDEX}}" disabled>
								<option value=""><?php esc_html_e( 'All records (default view)', 'airtable-sync' ); ?></option>
							</select>
							<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][{{INDEX}}][view_name]" value="" class="view-name-hidden" />
						</div>
						<span class="mapping-arrow">→</span>
						<div class="mapping-select-group">
							<label><?php esc_html_e( 'Post Type', 'airtable-sync' ); ?></label>
							<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][{{INDEX}}][post_type]" class="post-type-select" data-index="{{INDEX}}">
								<option value=""><?php esc_html_e( 'Select post type...', 'airtable-sync' ); ?></option>
								<?php foreach ( $post_types as $post_type ) : ?>
									<option value="<?php echo esc_attr( $post_type->name ); ?>">
										<?php echo esc_html( $post_type->labels->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="mapping-actions">
						<button type="button" class="button configure-fields" data-index="{{INDEX}}" disabled>
							<?php esc_html_e( 'Configure Fields', 'airtable-sync' ); ?>
						</button>
						<button type="button" class="button remove-mapping"><?php esc_html_e( 'Remove', 'airtable-sync' ); ?></button>
					</div>
				</div>
				<div class="field-mappings-container" data-index="{{INDEX}}" style="display:none;"></div>
			</div>
		</script>
		<?php
	}

	/**
	 * Render field mappings section for a table mapping.
	 *
	 * @param int   $index   The index of the table mapping.
	 * @param array $mapping The mapping data.
	 */
	private function render_field_mappings_section( $index, $mapping ) {
		$field_mappings = isset( $mapping['field_mappings'] ) ? $mapping['field_mappings'] : array();
		?>
		<div class="field-mappings-container" data-index="<?php echo esc_attr( $index ); ?>" style="display:none;">
			<div class="field-mappings-header">
				<h4><?php esc_html_e( 'Field Mappings', 'airtable-sync' ); ?></h4>
				<button type="button" class="button load-fields" data-index="<?php echo esc_attr( $index ); ?>">
					<?php esc_html_e( 'Load Fields', 'airtable-sync' ); ?>
				</button>
			</div>
			<div class="field-mappings-list" data-index="<?php echo esc_attr( $index ); ?>">
				<?php if ( ! empty( $field_mappings ) ) : ?>
					<?php foreach ( $field_mappings as $field_index => $field_mapping ) : ?>
						<div class="field-mapping-row" data-field-index="<?php echo esc_attr( $field_index ); ?>">
							<div class="field-mapping-airtable">
								<strong><?php echo esc_html( $field_mapping['airtable_field_name'] ); ?></strong>
								<span class="field-type">(<?php echo esc_html( $field_mapping['airtable_field_type'] ); ?>)</span>
								<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][airtable_field_id]" value="<?php echo esc_attr( $field_mapping['airtable_field_id'] ); ?>" />
								<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][airtable_field_name]" value="<?php echo esc_attr( $field_mapping['airtable_field_name'] ); ?>" />
								<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][airtable_field_type]" value="<?php echo esc_attr( $field_mapping['airtable_field_type'] ); ?>" />
							</div>
							<span class="field-mapping-arrow">→</span>
							<div class="field-mapping-destination">
								<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][destination_type]" class="destination-type-select">
									<option value=""><?php esc_html_e( 'Select type...', 'airtable-sync' ); ?></option>
									<option value="core" <?php selected( $field_mapping['destination_type'], 'core' ); ?>><?php esc_html_e( 'Core WordPress', 'airtable-sync' ); ?></option>
									<option value="taxonomy" <?php selected( $field_mapping['destination_type'], 'taxonomy' ); ?>><?php esc_html_e( 'Taxonomy', 'airtable-sync' ); ?></option>
									<option value="acf" <?php selected( $field_mapping['destination_type'], 'acf' ); ?>><?php esc_html_e( 'ACF Field', 'airtable-sync' ); ?></option>
								</select>
								<select name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][destination_key]" class="destination-field-select">
									<option value="<?php echo esc_attr( $field_mapping['destination_key'] ); ?>"><?php echo esc_html( $field_mapping['destination_name'] ); ?></option>
								</select>
								<input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[table_mappings][<?php echo esc_attr( $index ); ?>][field_mappings][<?php echo esc_attr( $field_index ); ?>][destination_name]" value="<?php echo esc_attr( $field_mapping['destination_name'] ); ?>" class="destination-name-hidden" />
							</div>
							<button type="button" class="button button-small remove-field-mapping">×</button>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="field-mappings-empty" style="<?php echo ! empty( $field_mappings ) ? 'display:none;' : ''; ?>">
				<p><?php esc_html_e( 'No field mappings configured. Click "Load Fields" to get started.', 'airtable-sync' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler to get Airtable bases.
	 */
	public function ajax_get_bases() {
		check_ajax_referer( 'airtable_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'airtable-sync' ) ) );
		}

		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';

		if ( empty( $api_key ) ) {
			wp_send_json_error( array( 'message' => __( 'API key is required.', 'airtable-sync' ) ) );
		}

		$response = wp_remote_get(
			'https://api.airtable.com/v0/meta/bases',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			wp_send_json_error( array( 'message' => $data['error']['message'] ) );
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX handler to get tables from a base.
	 */
	public function ajax_get_tables() {
		check_ajax_referer( 'airtable_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'airtable-sync' ) ) );
		}

		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
		$base_id = isset( $_POST['base_id'] ) ? sanitize_text_field( $_POST['base_id'] ) : '';

		if ( empty( $api_key ) || empty( $base_id ) ) {
			wp_send_json_error( array( 'message' => __( 'API key and base ID are required.', 'airtable-sync' ) ) );
		}

		$response = wp_remote_get(
			'https://api.airtable.com/v0/meta/bases/' . $base_id . '/tables',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			wp_send_json_error( array( 'message' => $data['error']['message'] ) );
		}

		wp_send_json_success( $data );
	}

	/**
	 * AJAX handler to get views from a table.
	 */
	public function ajax_get_views() {
		check_ajax_referer( 'airtable_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'airtable-sync' ) ) );
		}

		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
		$base_id = isset( $_POST['base_id'] ) ? sanitize_text_field( $_POST['base_id'] ) : '';
		$table_id = isset( $_POST['table_id'] ) ? sanitize_text_field( $_POST['table_id'] ) : '';

		if ( empty( $api_key ) || empty( $base_id ) || empty( $table_id ) ) {
			wp_send_json_error( array( 'message' => __( 'API key, base ID, and table ID are required.', 'airtable-sync' ) ) );
		}

		// Get table metadata which includes views
		$response = wp_remote_get(
			'https://api.airtable.com/v0/meta/bases/' . $base_id . '/tables',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			wp_send_json_error( array( 'message' => $data['error']['message'] ) );
		}

		// Find the specific table and return its views
		$views = array();
		if ( isset( $data['tables'] ) ) {
			foreach ( $data['tables'] as $table ) {
				if ( $table['id'] === $table_id ) {
					$views = isset( $table['views'] ) ? $table['views'] : array();
					break;
				}
			}
		}

		wp_send_json_success( array( 'views' => $views ) );
	}

	/**
	 * AJAX handler to get table schema (fields) from Airtable.
	 */
	public function ajax_get_table_schema() {
		check_ajax_referer( 'airtable_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'airtable-sync' ) ) );
		}

		$api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
		$base_id = isset( $_POST['base_id'] ) ? sanitize_text_field( $_POST['base_id'] ) : '';
		$table_id = isset( $_POST['table_id'] ) ? sanitize_text_field( $_POST['table_id'] ) : '';
		$view_id = isset( $_POST['view_id'] ) ? sanitize_text_field( $_POST['view_id'] ) : '';

		if ( empty( $api_key ) || empty( $base_id ) || empty( $table_id ) ) {
			wp_send_json_error( array( 'message' => __( 'API key, base ID, and table ID are required.', 'airtable-sync' ) ) );
		}

		$response = wp_remote_get(
			'https://api.airtable.com/v0/meta/bases/' . $base_id . '/tables',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['error'] ) ) {
			wp_send_json_error( array( 'message' => $data['error']['message'] ) );
		}

		// Find the specific table and return its fields
		$table_schema = null;
		if ( isset( $data['tables'] ) ) {
			foreach ( $data['tables'] as $table ) {
				if ( $table['id'] === $table_id ) {
					$table_schema = $table;
					break;
				}
			}
		}

		if ( ! $table_schema ) {
			wp_send_json_error( array( 'message' => __( 'Table not found.', 'airtable-sync' ) ) );
		}

		// If a view is specified, filter fields to only those visible in the view
		if ( ! empty( $view_id ) ) {
			// First, check if the metadata API provides visibleFieldIds
			$visible_field_ids = null;

			if ( isset( $table_schema['views'] ) ) {
				foreach ( $table_schema['views'] as $view ) {
					if ( $view['id'] === $view_id ) {
						if ( isset( $view['visibleFieldIds'] ) && is_array( $view['visibleFieldIds'] ) ) {
							$visible_field_ids = $view['visibleFieldIds'];
						}
						break;
					}
				}
			}

			// If metadata doesn't include visibleFieldIds, fetch records from the view
			// using returnFieldsByFieldId to get all visible field IDs (including empty ones)
			if ( $visible_field_ids === null ) {
				$sample_response = wp_remote_get(
					'https://api.airtable.com/v0/' . $base_id . '/' . $table_id . '?view=' . rawurlencode( $view_id ) . '&maxRecords=5&returnFieldsByFieldId=true',
					array(
						'headers' => array(
							'Authorization' => 'Bearer ' . $api_key,
						),
						'timeout' => 30,
					)
				);

				if ( ! is_wp_error( $sample_response ) ) {
					$sample_body = wp_remote_retrieve_body( $sample_response );
					$sample_data = json_decode( $sample_body, true );

					// When using returnFieldsByFieldId=true, the fields are returned with field IDs as keys
					// We'll collect all unique field IDs across multiple records to account for sparse data
					if ( isset( $sample_data['records'] ) && ! empty( $sample_data['records'] ) ) {
						$visible_field_ids = array();

						// Collect field IDs from all sample records
						foreach ( $sample_data['records'] as $record ) {
							if ( isset( $record['fields'] ) && is_array( $record['fields'] ) ) {
								foreach ( array_keys( $record['fields'] ) as $field_id ) {
									if ( ! in_array( $field_id, $visible_field_ids, true ) ) {
										$visible_field_ids[] = $field_id;
									}
								}
							}
						}
					}
				}
			}

			// Apply the filter if we have visible field IDs
			if ( $visible_field_ids && is_array( $visible_field_ids ) && ! empty( $visible_field_ids ) ) {
				$filtered_fields = array();

				foreach ( $table_schema['fields'] as $field ) {
					if ( in_array( $field['id'], $visible_field_ids, true ) ) {
						$filtered_fields[] = $field;
					}
				}

				$table_schema['fields'] = $filtered_fields;
			}
		}

		wp_send_json_success( $table_schema );
	}

	/**
	 * AJAX handler to get WordPress fields (core, ACF, and taxonomies) for a post type.
	 */
	public function ajax_get_wp_fields() {
		check_ajax_referer( 'airtable_sync_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'airtable-sync' ) ) );
		}

		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

		if ( empty( $post_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Post type is required.', 'airtable-sync' ) ) );
		}

		$fields = array(
			'core' => array(),
			'taxonomies' => array(),
			'acf' => array(),
		);

		// Core WordPress fields
		$fields['core'] = array(
			array(
				'key' => 'post_title',
				'name' => __( 'Post Title', 'airtable-sync' ),
				'type' => 'text',
			),
			array(
				'key' => 'post_content',
				'name' => __( 'Post Content', 'airtable-sync' ),
				'type' => 'textarea',
			),
			array(
				'key' => 'post_excerpt',
				'name' => __( 'Post Excerpt', 'airtable-sync' ),
				'type' => 'textarea',
			),
			array(
				'key' => 'post_name',
				'name' => __( 'Post Slug', 'airtable-sync' ),
				'type' => 'text',
			),
			array(
				'key' => 'post_date',
				'name' => __( 'Post Date', 'airtable-sync' ),
				'type' => 'date',
			),
		);

		// Get taxonomies for the post type
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		foreach ( $taxonomies as $taxonomy ) {
			$fields['taxonomies'][] = array(
				'key' => $taxonomy->name,
				'name' => $taxonomy->labels->name,
				'type' => 'taxonomy',
				'hierarchical' => $taxonomy->hierarchical,
			);
		}

		// Get ACF fields for the post type
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups( array(
				'post_type' => $post_type,
			) );

			foreach ( $field_groups as $field_group ) {
				$acf_fields = acf_get_fields( $field_group['ID'] );
				if ( $acf_fields ) {
					foreach ( $acf_fields as $acf_field ) {
						$fields['acf'][] = array(
							'key' => $acf_field['key'],
							'name' => $acf_field['label'],
							'field_name' => $acf_field['name'],
							'type' => $acf_field['type'],
							'group' => $field_group['title'],
						);
					}
				}
			}
		}

		wp_send_json_success( $fields );
	}
}
