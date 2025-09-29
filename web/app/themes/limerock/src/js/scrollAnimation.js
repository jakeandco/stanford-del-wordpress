function setupScrollAnimation() {
  const elements = document.querySelectorAll(".block-wrapper:not(.hero-homepage)");
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      }
    });
  }, { 
    threshold: 0.05,
    rootMargin: '100px'
  });

  elements.forEach(el => observer.observe(el));
}

export function setup() {
  setupScrollAnimation();
}

export function teardown() {
  // No cleanup needed for this observer
} 