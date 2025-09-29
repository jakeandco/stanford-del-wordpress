export function setup() {
  const buttonTags = document.querySelectorAll('button.tag');

  for (const button of buttonTags) {
    button.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      window.location.href = button.dataset.url;
    });
  }
}

export function teardown() {}
