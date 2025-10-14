import readline from 'node:readline';
import pluralize from 'pluralize';
import { makeConvertFile, makeSimplePromptHandler } from '../skel-helpers.mjs';

function replaceWithOptions(stringToReplace, { name, slug, plural, singular, icon, link } = {}) {
  return stringToReplace
    .replaceAll('POSTTYPESLUG', slug)
    .replaceAll('POSTTYPENAME', name)
    .replaceAll('POSTTYPEPLURAL', plural)
    .replaceAll('POSTTYPESINGULAR', singular)
    .replaceAll('POSTTYPEICON', icon)
    .replaceAll('POSTTYPELINK', link)
    .replaceAll('TIMESTAMP', Number(Date.now().toString().slice(0, 10)))
}

const convertFile = makeConvertFile(import.meta.dirname, replaceWithOptions);

async function makeFiles(rl, options) {
  const acfDirArray = ['acf-json'];
  const acfComposerLibArray = ['lib', 'acf-composer'];

  let keepGoing = await convertFile(rl, 'post_type_POSTTYPESLUG.json', acfDirArray, options);
  if (keepGoing) keepGoing = await convertFile(rl, 'POSTTYPESLUG.json', [...acfComposerLibArray, 'post-types'], options);

  rl.close();
}


function askForPrompt(rl, options = {}, fullOptionsKeys) {
  const handleSimpleResponse = makeSimplePromptHandler(rl, options, fullOptionsKeys, askForPrompt)

  if (
    fullOptionsKeys.reduce(
      (hasKeys, currentKey) => hasKeys && Object.keys(options).includes(currentKey),
      true
    )
  ) {
    makeFiles(rl, options);
  } else if (!options.name) {
    rl.question(
      `Enter Name: `,
      handleSimpleResponse('name')
    );

  } else if (!options.slug) {
    const defaultAnswer = pluralize.singular(options.name).toLowerCase().replaceAll(' ', '-');
    rl.question(
      `Enter slug (default: ${defaultAnswer}): `,
      handleSimpleResponse('slug', defaultAnswer)
    );
  } else if (!options.singular) {
    const defaultAnswer = pluralize.singular(options.name);
    rl.question(
      `Enter Singular Label (default: ${defaultAnswer}): `,
      handleSimpleResponse('singular', defaultAnswer)
    );
  } else if (!options.plural) {
    const defaultAnswer = pluralize(options.singular);

    rl.question(
      `Enter Plural Label (default: ${defaultAnswer}): `,
      handleSimpleResponse('plural', defaultAnswer)
    );
  } else if (!options.icon) {
    const defaultAnswer = "admin-post";
    rl.question(
      `Enter Icon (default: ${defaultAnswer}): `,
      handleSimpleResponse('icon', defaultAnswer)
    );
  } else if (!options.link) {
    const defaultAnswer = options.slug;
    rl.question(
      `Enter Custom Permalink (default: ${defaultAnswer}): `,
      handleSimpleResponse('link', defaultAnswer)
    );
  }
}

/**
 * @param {typeof import('commander').Command} program
 */
export function generator(program) {
  program
    .option('-n, --name <string>', "Post Type Label")
    .option('-s, --slug <string>', "Post Type Slug. Use kebab-case-names-please")
    .option('-p, --plural <string>', "Plural Label")
    .option('--singular <string>', "Singular Label")
    .option('-i, --icon <string>', "Menu Bar Icon")
    .option('-l, --link <string>', "Custom Permalink")
    .action(function () {
      askForPrompt(readline.createInterface({
        input: process.stdin,
        output: process.stdout,
      }), this.opts(), this.options.map(opt => opt.long.replace('--', '')));
    })
}
