import { Fancybox } from "@fancyapps/ui";

export function setup() {
    console.log('fancybox');

    Fancybox.bind("[data-fancybox]", {
        hideScrollbar: false,
        Thumbs: false,
        // Carousel: {
        //     infinite: false
        // },
        // on: {
        //     "Carousel.ready Carousel.change": (fancybox) => {
        //         const slide = fancybox.getSlide();
        //         if (!slide) return;

        //         const fancyboxContent = document.querySelector(".is-selected .fancybox__content");
        //         if (fancyboxContent && !fancyboxContent.querySelector(".caption")) {
        //             fancyboxContent.insertAdjacentHTML('beforeend', '<div class="caption container">' + slide.caption + '</div>');
        //         }

        //         const carousel = fancybox.Carousel;
        //         if (!carousel) return;

        //         const currentIndex = slide.index;
        //         const totalSlides = carousel.slides.length;

        //         const prevBtn = document.querySelector(".fancybox__nav .is-prev");
        //         const nextBtn = document.querySelector(".fancybox__nav .is-next");

        //         if (prevBtn) {
        //             prevBtn.classList.toggle("is-disabled", currentIndex === 0);
        //             prevBtn.toggleAttribute("disabled", currentIndex === 0);
        //         }

        //         if (nextBtn) {
        //             nextBtn.classList.toggle("is-disabled", currentIndex === totalSlides - 1);
        //             nextBtn.toggleAttribute("disabled", currentIndex === totalSlides - 1);
        //         }
        //     }
        // }
    });
}

export function teardown() {
    
}