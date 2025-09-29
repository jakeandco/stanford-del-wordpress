import acfFields from './acf-fields.js';
import * as swiper from '../swiper';
import * as accordion from '../accordion';

document.onreadystatechange = function () {
  if (document.readyState == 'complete' && typeof acf !== 'undefined') {
    acfFields.setup();
    swiper.setup();
    accordion.setup();

    if (acf.addAction) {
      acf.addAction('render_block_preview', function ($el, attributes, block) {
        swiper.setup($el[0]);
        accordion.setup($el[0]);
      });
    }
  }
};
