import readline from 'node:readline';
import { makeConvertFile } from '../skel-helpers.mjs';

function replaceWithOptions(stringToReplace, { name, slug } = {}) {
  return stringToReplace
    .replaceAll('OPTIONSSLUG', slug)
    .replaceAll('OPTIONSNAME', name)
    .replaceAll('TIMESTAMP', Number(Date.now().toString().slice(0, 10)))
}

const convertFile = makeConvertFile(import.meta.dirname, replaceWithOptions);

async function makeFiles(rl, options) {
  const acfDirArray = ['acf-json'];

  let keepGoing = await convertFile(rl, 'ui_options_page_OPTIONSSLUG.json', acfDirArray, options);
  if (keepGoing) keepGoing = await convertFile(rl, 'group_options_OPTIONSSLUG.json', acfDirArray, options);

  rl.close();
}


function askForPrompt(rl, options = {}, fullOptionsKeys) {
  if (
    fullOptionsKeys.reduce(
      (hasKeys, currentKey) => hasKeys && Object.keys(options).includes(currentKey),
      true
    )
  ) {
    makeFiles(rl, options);
  } else if (!options.name) {
    rl.question(`Enter Name: `, name => {
      askForPrompt(rl, {...options, name }, fullOptionsKeys)
    });

  } else if (!options.slug) {
    const defaultAnswer = options.name.toLowerCase().replaceAll(' ', '-');
    rl.question(
      `Enter slug (default: ${defaultAnswer}): `,
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
    .option('-n, --name <string>', "Options Page Name")
    .option('-s, --slug <string>', "Options Slug. Use kebab-case-names-please")
    .action(function () {
      askForPrompt(readline.createInterface({
        input: process.stdin,
        output: process.stdout,
      }), this.opts(), this.options.map(opt => opt.long.replace('--', '')));
    })
}