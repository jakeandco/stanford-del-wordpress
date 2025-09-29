import readline from 'node:readline';
import { ChatTree, makeConvertFile, strictYes, truthyOptions, doFieldBuilderChat, buildFieldsHTML } from '../skel-helpers.mjs';

const doOptionsChat = async (rl, { responses, requiredResponses }) => {
  const optionsChatTree = new ChatTree(rl, {
    requiredResponses: [...requiredResponses],
    responses,
    tree: {
      "name": {
        prompt: () => `Enter Name: `,
      },
      "slug": {
        defaultAnswer: ({ responses }) => responses.name.toLowerCase().replaceAll(' ', '-'),
        prompt: ({ defaultAnswer }) => `Enter slug (default: ${defaultAnswer}): `,
      },
      "link": {
        defaultAnswer: "#",
        prompt: ({ defaultAnswer }) => `Enter Design Link (default: ${defaultAnswer}): `,
      },
      "usePostFields": {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `Add post meta ACF fields? (default: ${defaultAnswer}): `,
        formatAnswer: ({ userAnswer, defaultAnswer }) => truthyOptions.includes(userAnswer) || truthyOptions.includes(defaultAnswer),
        addResponse: async ({ answer: usePostFields, responses }) => {
          if (typeof usePostFields !== 'boolean') return { ...responses, usePostFields, fields: [] };
          const fields = await doFieldBuilderChat(rl);
          return { ...responses, usePostFields, fields };
        },
      },
    }
  });

  return optionsChatTree.start();
}

const doFilesChat = async (rl, options) => {
  const blockDirArray = ['views', 'parts', options.slug];

  function replaceWithOptions(stringToReplace, { name, slug, link, fields } = {}) {
    return stringToReplace
      .replaceAll('BLOCKSLUG', slug)
      .replaceAll('BLOCKCONTENT', fields.length ? buildFieldsHTML(fields, 'post') : '{{fields|console_log}}')
      .replaceAll('BLOCKNAME', name)
      .replaceAll('DESIGNLINK', link)
      .replaceAll('TIMESTAMP', Number(Date.now().toString().slice(0, 10)))
  }

  const convertFile = makeConvertFile(import.meta.dirname, replaceWithOptions);

  const filesChatTree = new ChatTree(rl, {
    initialStep: 'template',
    responses: options,
    requiredResponses: [],
    tree: {
      "template": {
        do: () => convertFile(rl, 'BLOCKSLUG.twig', blockDirArray, options),
        navigateTo({ doResult }) {
          if (doResult) {
            return 'stories';
          }
          return ChatTree.end;
        }
      },
      "stories": {
        do: () => convertFile(rl, 'BLOCKSLUG.stories.js', blockDirArray, options),
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
    .option('-l, --link <string>', "Design Link")
    .option('-f, --usePostFields <string>', "Use ACF Post Meta Fields")
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