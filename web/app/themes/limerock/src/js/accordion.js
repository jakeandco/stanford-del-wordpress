import Collapse from 'bootstrap/js/dist/collapse';

export function setup() {
  const collapseElementList = document.querySelectorAll('.collapse');
  const header = document.querySelector('#header');
  const navbarCollapseElement = document.querySelectorAll('.navbar-collapse');

  // Create collapse instance
  for (const collapseElement of collapseElementList) {
    let inner_jump_links = [];
    const collapseInstance = new Collapse(collapseElement, {
      toggle: false,
    });

    document.addEventListener('click', function (event) {
      const button = document.querySelector(
        '.accordion-button[aria-expanded="false"]'
      );

      if (
        collapseElement.classList.contains('show') &&
        !collapseElement.contains(event.target) &&
        !button.contains(event.target) &&
        !inner_jump_links.includes(event.target)
      ) {
        collapseInstance.hide();
      }
    });

    for (const body_el of collapseElement.getElementsByClassName(
      'accordion-body'
    )) {
      const potential_jump_targets = body_el.querySelectorAll('[id]');

      for (const potential_jump_target of potential_jump_targets) {
        if (
          window.location.hash &&
          window.location.hash.replace('#', '') === potential_jump_target.id
        ) {
          document.addEventListener('readystatechange', (e) => {
            if (e.target.readyState == 'complete') {
              collapseInstance.show();
              potential_jump_target.scrollIntoView();
            }
          });
        }

        const actual_jump_links = document.querySelectorAll(
          `[href="#${potential_jump_target.id}"]`
        );

        for (const actual_jump_link of actual_jump_links) {
          inner_jump_links.push(actual_jump_link);

          actual_jump_link.addEventListener('click', (e) => {
            requestAnimationFrame(() => {
              if (!collapseInstance._isShown()) {
                collapseInstance.show();
              }

              potential_jump_target.scrollIntoView();
            });
          });
        }
      }
    }
  }

  navbarCollapseElement.forEach((collapseElement) => {
    collapseElement.addEventListener('show.bs.collapse', () => {
      header.classList.add('collapse-open');
    });

    collapseElement.addEventListener('hide.bs.collapse', () => {
      header.classList.remove('collapse-open');
    });
  });
}

export function teardown() {}
