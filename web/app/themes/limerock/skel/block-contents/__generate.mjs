import readline from 'node:readline';
import path from 'node:path';
import blockFromACF from './block-from-acf.mjs';
import { makeConvertFile } from '../skel-helpers.mjs';

function replaceWithOptions(stringToReplace, {generatedContents} = {}) {
  return stringToReplace
    .replaceAll('BLOCKCONTENT', generatedContents)
}

async function replaceFile(rl, options) {
  const blockDirArray = ['blocks', options.slug];
  const convertFile = makeConvertFile(
    path.join(import.meta.dirname, '../../views', ...blockDirArray),
    replaceWithOptions
  );

  const jsonPath = path.join(import.meta.dirname, '../../views', ...blockDirArray, 'acf-composed.json');

  const generatedContents = blockFromACF(jsonPath);

  await convertFile(rl, `${options.slug}.twig`, blockDirArray, { ...options, generatedContents });

  rl.close();
}


function askForPrompt(rl, options = {}, fullOptionsKeys) {
  if (
    fullOptionsKeys.reduce(
      (hasKeys, currentKey) => hasKeys && Object.keys(options).includes(currentKey),
      true
    )
  ) {
    replaceFile(rl, options);
  } else if (!options.slug) {
    rl.question(
      `Enter slug: `,
      (userAnswer) => {
        askForPrompt(rl, { ...options, slug: userAnswer || defaultAnswer }, fullOptionsKeys)
      });
  }
}

/**
 * @param {typeof import('commander').Command} program
 */
export function generator(program) {
  program
    .option('-s, --slug <string>', "Block Slug. Use kebab-case-names-please")
    .action(function () {
      askForPrompt(readline.createInterface({
        input: process.stdin,
        output: process.stdout,
      }), this.opts(), this.options.map(opt => opt.long.replace('--', '')));
    })
}