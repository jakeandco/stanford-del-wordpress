export function setup() {
  let playerOpeners = document.querySelectorAll('.video-toggle');

  for (const elem of playerOpeners) {
    const toggleOverlay = elem,
      videoHolder = toggleOverlay.closest('.media-item_holder'),
      video = videoHolder.querySelector('video');

    function listenForPlay(e) {
      e.stopPropagation();
      videoHolder.classList.add('play');
      video.play();

      toggleOverlay.removeEventListener('click', listenForPlay);
      videoHolder.addEventListener('click', listenForPause);
    }

    function listenForPause(e) {
      e.stopPropagation();
      videoHolder.classList.remove('play');
      video.pause();

      videoHolder.removeEventListener('click', listenForPause);
      toggleOverlay.addEventListener('click', listenForPlay);
    }

    toggleOverlay.addEventListener('click', listenForPlay);
  }
}

export function teardown() {}
