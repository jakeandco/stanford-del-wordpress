import * as accordion from './accordion';
import * as customSelect from './customSelect';
import * as dropdown from './dropdown';
import * as fancybox from './fancybox';
import * as footnotes from './footnotes';
import * as scrollAnimation from './scrollAnimation';
import * as share from './share';
import * as swiper from './swiper';
import * as tags from './tags';
import * as video from './video';

export function setup() {
  accordion.setup();
  customSelect.setup();
  dropdown.setup();
  fancybox.setup();
  footnotes.setup();
  scrollAnimation.setup();
  share.setup();
  swiper.setup();
  tags.setup();
  video.setup();
}

// necessary for storybook to use this file in its entirety
export function teardown() {
  accordion.teardown();
  customSelect.teardown();
  dropdown.teardown();
  fancybox.teardown();
  footnotes.teardown();
  scrollAnimation.teardown();
  share.teardown();
  swiper.teardown();
  tags.teardown();
  video.teardown();
}

document.addEventListener('DOMContentLoaded', setup, false);
