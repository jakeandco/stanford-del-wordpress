import { Command } from 'commander';
import * as block from './block/__generate.mjs';
import * as blockContents from './block-contents/__generate.mjs';
import * as part from './part/__generate.mjs';
import * as taxonomy from './taxonomy/__generate.mjs';
import * as postType from './post-type/__generate.mjs';
import * as optionsPage from './options-page/__generate.mjs';

const program = new Command();
program
  .name('generate')
  .description('CLI to help generate theme files')
  .version('0.0.0');

block.generator(
  program
    .command('block')
    .description('Build a block')
);

postType.generator(
  program
    .command('post-type')
    .description('Build a WordPress Post Type')
);

optionsPage.generator(
  program
    .command('options')
    .description('Build an ACF Options page')
);

blockContents.generator(
  program
    .command('block-contents')
    .description('Build out a block\'s template from ACF fields')
);

part.generator(
  program
    .command('part')
    .description('Build a part')
);

taxonomy.generator(
  program
    .command('taxonomy')
    .description('Build a taxonomy')
);

program.parse();