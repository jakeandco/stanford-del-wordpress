/**
 * Initialize the interior subnavigation component
 */
export function setup() {
  for (const shareLink of document.getElementsByClassName('share-link')) {
    shareLink.addEventListener('click', (e) => navigator.share({
      url: e.target.dataset.url,
      text: e.target.dataset.text
    }))
  }
}

export function teardown() {}
