import Popover from 'bootstrap/js/dist/popover';

const selectors = {
  link: 'doi-link',
  popover: 'doi-link--popover',
};

let allLinks = [];

class DOILink {
  popover;
  element;

  constructor(element) {
    this.element = element;
    this.popover = new Popover(element, {
      customClass: selectors.popover,
      trigger: 'manual',
      content: 'Copied to clipboard',
      container: 'body',
    });

    this.element.addEventListener('click', this.handleClick);
  }

  handleClick = async (e) => {
    e.preventDefault();

    try {
      await navigator.clipboard.writeText(this.element.href);
      this.popover.show();

      setTimeout(() => {
        this.popover.hide();
      }, 2000);
    } catch (error) {
      console.error(error.message);
    }
  };

  tearDown = () => {
    element.removeEventListener('click', copyOnClick);
    this.popover.dispose();
  }
}

/**
 * Initialize the interior subnavigation component
 */
export function setup() {
  const doiLinks = document.getElementsByClassName(selectors.link);

  for (const link of Array.from(doiLinks)) {
    allLinks.push(new DOILink(link));
  }
}

export function teardown() {
  for (const link of allLinks) {
    link.tearDown();
  }

  allLinks = [];
}
