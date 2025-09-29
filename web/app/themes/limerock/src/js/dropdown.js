import Dropdown from 'bootstrap/js/dist/dropdown';

/**
 * Initialize the interior subnavigation component
 */
export function setup() {
  console.log('dropdown');
  
  // Initialize collapse for the table of contents
  const header = document.querySelector('#header');
  const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
  const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new Dropdown(dropdownToggleEl));

  const dropdowns = document.querySelectorAll('.dropdown');

  dropdowns.forEach(dropdown => {
    dropdown.addEventListener('show.bs.dropdown', () => {
      header.classList.add('dropdown-open');
      // const menu = dropdown.querySelector('.dropdown-menu');
      // console.log(menu);
      // menu.classList.add('animating');
      // setTimeout(() => menu.classList.add('showing'), 10);
    });

    dropdown.addEventListener('hide.bs.dropdown', () => {
      header.classList.remove('dropdown-open');
      // const menu = dropdown.querySelector('.dropdown-menu');
      // menu.classList.remove('showing');
      // setTimeout(() => menu.classList.remove('animating'), 250);
    });
  });

}

export function teardown() {
  
} 