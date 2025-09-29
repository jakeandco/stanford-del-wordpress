import readline from 'node:readline';
import { ChatTree, makeConvertFile, strictYes, truthyOptions, doBlockSupportsChat, doFieldBuilderChat, buildFieldsHTML } from '../skel-helpers.mjs';

const doOptionsChat = async (rl, { responses, requiredResponses }) => {
  const optionsChatTree = new ChatTree(rl, {
    requiredResponses: [...requiredResponses, 'supports'],
    responses,
    tree: {
      "name": {
        prompt: () => `Enter Name: `,
      },
      "slug": {
        defaultAnswer: ({ responses }) => responses.name.toLowerCase().replaceAll(' ', '-'),
        prompt: ({ defaultAnswer }) => `Enter slug (default: ${defaultAnswer}): `,
      },
      "desc": {
        defaultAnswer: "Block Description",
        prompt: ({ defaultAnswer }) => `Enter description (default: ${defaultAnswer}): `,
      },
      "cat": {
        defaultAnswer: "theme",
        prompt: ({ defaultAnswer }) => `Enter Category (default: ${defaultAnswer}): `,
      },
      "icon": {
        defaultAnswer: "admin-comments",
        prompt: ({ defaultAnswer }) => `Enter Icon (default: ${defaultAnswer}): `,
      },
      "link": {
        defaultAnswer: "#",
        prompt: ({ defaultAnswer }) => `Enter Design Link (default: ${defaultAnswer}): `,
      },
      "composeFields": {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `Use acf-field-group-composer? (default: ${defaultAnswer}): `,
        formatAnswer: ({ userAnswer, defaultAnswer }) => truthyOptions.includes(userAnswer) || truthyOptions.includes(defaultAnswer),
        addResponse: async ({ answer: composeFields, responses }) => {
          if (typeof composeFields !== 'boolean') return { ...responses, composeFields, fields: [] };
          const fields = await doFieldBuilderChat(rl);
          return { ...responses, composeFields, fields };
        },
      },
      "supports": {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `Build out block supports? (default: ${defaultAnswer}): `,
        formatAnswer: ({ userAnswer, defaultAnswer }) => truthyOptions.includes(userAnswer) || truthyOptions.includes(defaultAnswer),
        addResponse: async ({ answer, responses }) => {
          if (typeof answer !== 'boolean') return { ...responses, supports: {} };
          const supports = await doBlockSupportsChat(rl);
          return { ...responses, supports };
        },
      }
    }
  });

  return optionsChatTree.start();
}

const doFilesChat = async (rl, options) => {
  const blockDirArray = ['views', 'blocks', options.slug];
  const acfDirArray = ['acf-json'];

  function replaceWithOptions(stringToReplace, { name, slug, desc, cat, icon, supports, fields } = {}) {
    return stringToReplace
      .replaceAll('BLOCKSLUG', slug)
      .replaceAll('BLOCKSUPPORTS', JSON.stringify(supports, undefined, 2))
      .replaceAll('BLOCKFIELDS', JSON.stringify(fields, undefined, 2))
      .replaceAll('BLOCKCONTENT', fields.length ? buildFieldsHTML(fields) : '{{fields|console_log}}')
      .replaceAll('BLOCKNAME', name)
      .replaceAll('BLOCKDESCRIPTION', desc)
      .replaceAll('BLOCKCATEGORY', cat)
      .replaceAll('BLOCKICON', icon)
      .replaceAll('TIMESTAMP', Number(Date.now().toString().slice(0, 10)))
  }

  const convertFile = makeConvertFile(import.meta.dirname, replaceWithOptions);

  const filesChatTree = new ChatTree(rl, {
    initialStep: 'block',
    responses: options,
    requiredResponses: [],
    tree: {
      "block": {
        do: () => convertFile(rl, 'block.json', blockDirArray, options),
        navigateTo({ doResult }) {
          if (doResult) return 'template';
          return ChatTree.end;
        }
      },
      "template": {
        do: () => convertFile(rl, 'BLOCKSLUG.twig', blockDirArray, options),
        navigateTo({ doResult, responses: { composeFields } }) {
          if (doResult) {
            if (composeFields) {
              return 'acf-composed';
            } else {
              return 'acf-field';
            }
          }
          return ChatTree.end;
        }
      },
      "acf-field": {
        do: () => convertFile(rl, 'group_block_BLOCKSLUG.json', acfDirArray, options),
        navigateTo({ doResult }) {
          if (doResult) return 'template'
          return ChatTree.end;
        }
      },
      "acf-composed": {
        do: () => convertFile(rl, 'acf-composed.json', blockDirArray, options),
        navigateTo({ doResult }) {
          if (doResult) return 'styles'
          return ChatTree.end;
        }
      },
      "styles": {
        do: () => convertFile(rl, 'BLOCKSLUG.scss', blockDirArray, options),
        navigateTo: ChatTree.end
      }
    }
  });

  return filesChatTree.start();
}

export async function chatBot(rl, initialConfig) {
  const chatBot = new ChatTree(rl, {
    responses: {},
    requiredResponses: ['options'],
    tree: {
      'options': {
        async do() {
          const options = await doOptionsChat(rl, initialConfig);
          chatBot.setResponses(options);
        },
        navigateTo: 'files'
      },
      'files': {
        do: ({ responses }) => doFilesChat(rl, responses),
        navigateTo: ChatTree.end
      }
    }
  });

  return chatBot.start();
}


/**
 * @param {typeof import('commander').Command} program
 */
export function generator(program) {
  program
    .option('-n, --name <string>', "Block Name")
    .option('-s, --slug <string>', "Block Slug. Use kebab-case-names-please")
    .option('-d, --desc <string>', "Block Description")
    .option('-c, --cat <string>', "Block Category")
    .option('-i, --icon <string>', "Block Icon")
    .option('-f, --composeFields <string>', "Compose Fields")
    .action(async function () {
      const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
      });

      await chatBot(rl, {
        responses: this.opts(),
        requiredResponses: this.options.map(opt => opt.long.replace('--', ''))
      });

      rl.close();
    })
}