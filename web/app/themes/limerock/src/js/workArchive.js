export function setup() {
  const form = document.querySelector('.ajax-form-js');
  if (!form) return;

  const resultsContainer = form.querySelector('.ajax-results-js');
  const filterOptions = form.querySelectorAll('.filter-option-js');
  const selectedFiltersWrap = form.querySelector('.selected-filters-js');
  const resetBtns = form.querySelectorAll('.reset-js');
  const defaultSortJs = form.querySelector('.default-sort-js');
  let currentController = null;

  updSelectedFiltersLabels();

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const action = form.getAttribute('action');
    const searchParams = new URLSearchParams(new FormData(form)).toString();
    const url = action + '?' + searchParams;

    ajaxLoadPosts(url);
  });

  filterOptions.forEach(option => {
    option.addEventListener('change', () => {
      handleAllFilterOption(option);
      triggerSubmit();
    });
  });

  // handle pagination ajax
  form.addEventListener('click', function (e) {
    const link = e.target.closest('.pagination-js a');
    if (!link) return;

    e.preventDefault();

    const url = link.getAttribute('href');

    ajaxLoadPosts(url);
    scrollToResults(resultsContainer);
  });

  // remove filter
  selectedFiltersWrap.addEventListener('click', function (e) {
    const removeOption = e.target.closest('.remove-filter-js');

    if (!removeOption) return;

    const optionId = removeOption.getAttribute('data-option');
    const relatedOption = form.querySelector(`.filter-option-js[data-option="${optionId}"]`);

    if (relatedOption) {
      relatedOption.checked = false;
      relatedOption.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  // handle reset
  resetBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      form.reset(); // reset search

      // uncheck all filter options except the default sort
      filterOptions.forEach(input => {
        if (input.classList.contains('default-sort-js')) return;

        input.checked = false;
      });

      setDefaultSortOption();
      triggerSubmit();

    });
  });

  function ajaxLoadPosts(url) {
    if (currentController) {
      currentController.abort();
    }

    currentController = new AbortController();
    const signal = currentController.signal;

    form.classList.add('ajax-loading');

    updSelectedFiltersLabels();

    fetch(url, { method: 'GET', signal })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
      })
      .then(html => {
        updateGridContent(html);
        history.pushState(null, '', url);
      })
      .catch(err => {
        if (err.name !== 'AbortError') {
          console.error('Fetch error:', err);
        }
      })
      .finally(() => {
        form.classList.remove('ajax-loading');
        currentController = null;
      });
  }

  function updateGridContent(response) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = response;

    const newGrid = tempDiv.querySelector('.ajax-results-js');

    if (newGrid && resultsContainer) {
      resultsContainer.innerHTML = newGrid.innerHTML;
    }
  }

  function updSelectedFiltersLabels() {
    if (!selectedFiltersWrap) return;

    let selectedFiltersHTML = '';

    filterOptions.forEach(option => {
      const defaultSortOption = option.classList.contains('default-sort-js');
      const allOption = option.classList.contains('all-labels-js');
      const isChecked = option.checked;

      if (!defaultSortOption && !allOption && isChecked) {
        const optionId = option.getAttribute('data-option');
        const optionText = option.getAttribute('data-text');
        selectedFiltersHTML += createLabel(optionText, optionId);
      }
    });

    const noLabels = selectedFiltersHTML === '';

    resetBtns.forEach(btn => {
      btn.disabled = noLabels;
    });

    if (noLabels) {
      form.classList.add('hide-selected-labels')
    } else {
      form.classList.remove('hide-selected-labels')
    }

    selectedFiltersWrap.innerHTML = selectedFiltersHTML;
  }

  function createLabel(text, label) {
    return `<div class="selected-filter">
      <div class="text">${text}</div>
      <button
        type="button"
        class="remove-filter-js"
        aria-label="Remove filter"
        data-option="${label}"
      >Remove filter</button>
    </div>`;
  }

  function triggerSubmit() {
    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
  }

  function scrollToResults(containerToScrollTo) {
    const header = document.getElementById('header');
    const headerHeight = header ? header.offsetHeight : 0;
    const offsetTop = containerToScrollTo.getBoundingClientRect().top + window.scrollY - headerHeight;

    window.scrollTo({
      top: offsetTop,
      behavior: 'smooth'
    });
  }

  function handleAllFilterOption(option) {
    const fieldWrap = option.closest('.labels-js');

    if (!fieldWrap) return;

    const allOption = fieldWrap.querySelector('.all-labels-js');

    if (!allOption) return;

    const optionIsChecked = option.checked;

    // clicked "All" option
    if (option.classList.contains('all-labels-js')) {
      const otherOptions = Array.from(fieldWrap.querySelectorAll('input[type="checkbox"]'))
        .filter(input => input !== allOption);

      otherOptions.forEach(input => input.checked = optionIsChecked);
      return;
    }

    // clicked not "All" option
    const otherOptions = Array.from(fieldWrap.querySelectorAll('input[type="checkbox"]'))
      .filter(input => !input.classList.contains('all-labels-js'));

    const allChecked = otherOptions.every(input => input.checked);
    allOption.checked = allChecked;
  }

  function setDefaultSortOption() {
    if (defaultSortJs) {
      defaultSortJs.checked = true;
    }
  }

}

export function teardown() {}
