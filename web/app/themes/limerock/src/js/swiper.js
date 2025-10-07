import { Swiper } from 'swiper';
import { Autoplay } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/autoplay'; // Import Autoplay CSS


export function setup() {
  console.log('swiper');

  const swiper = new Swiper('.swiper-container', {
    modules: [Autoplay],
    direction: 'vertical',
    effect: 'slide',
    slidesPerView: 1,
    loop: true,
    autoplay: {
      delay: 3000,
      reverseDirection: false,
      disableOnInteraction: false,
    },
  });
}

export function teardown() {}
