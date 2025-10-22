

function setupSvgAnimation() {
  console.log('Svg Animation');

  function initAnimations() {
    const holders = document.querySelectorAll('.image-holder.style-svg');
    console.log('Found holders:', holders.length);

    holders.forEach((holder) => {
      const svg = holder.querySelector('svg');
      if (!svg) return;

      const animations = svg.querySelectorAll('animateTransform');
      if (!animations.length) return;

      holder.addEventListener('mouseenter', () => {
        animations.forEach((anim) => anim.beginElement());
      });

      holder.addEventListener('mouseleave', () => {
        animations.forEach((anim) => anim.endElement());
      });
    });
  }

  function watchForInlineSVGs() {
    const observer = new MutationObserver(() => {
      if (document.querySelector('.style-svg svg')) {
        console.log('SVG detected via MutationObserver');
        initAnimations();
        observer.disconnect();
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.querySelector('.style-svg svg')) {
    console.log('SVG already in DOM');
    initAnimations();
  } else {
    document.addEventListener('svgsInlineReady', function () {
      console.log('SVGs inlined, init animations...');
      initAnimations();
    });

    watchForInlineSVGs();
  }
}

export function setup() {
  setupSvgAnimation();
}

export function teardown() {
  // No cleanup needed for this observer
}
