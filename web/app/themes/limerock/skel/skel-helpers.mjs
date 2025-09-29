import fs from 'node:fs';
import path from 'node:path';
import { replaceClonedFieldDefinitions } from '../util/acf.mjs';

export function makeSimplePromptHandler(
  rl,
  options,
  fullOptionsKeys,
  callback
) {
  return (key, defaultAnswer) => (userAnswer) =>
    callback(
      rl,
      { ...options, [key]: userAnswer || defaultAnswer },
      fullOptionsKeys
    );
}

export function makeConvertFile(dirname, replacerFn) {
  return async function convertFile(
    rl,
    filename,
    toDirArray,
    options,
    removeInFileName = ''
  ) {
    const fileContents = fs
      .readFileSync(path.join(dirname, `./${filename}`))
      .toString();

    const fileName = replacerFn(filename, options);
    const contents = replacerFn(fileContents, options);

    const dirPath = path.join(
      dirname,
      '../..',
      toDirArray.map((dir) => `/${dir}`).join('')
    );

    const fullPath = path.join(
      dirPath,
      `/${fileName.replace(removeInFileName, '')}`
    );

    const shouldWrite = await new Promise((resolve) => {
      console.log('checking file...', fullPath);
      try {
        fs.accessSync(fullPath, fs.constants.R_OK | fs.constants.W_OK);
        console.error(
          'File already exists! This tool is for creating new files only'
        );

        rl.question(`Enter "${strictYes}" to overwrite: `, (overWrite) => {
          if (overWrite === strictYes) {
            resolve(true);
          } else {
            resolve(false);
          }
        });
      } catch (err) {
        console.log('Good. File does not exist.');
        resolve(true);
      }
    });

    if (shouldWrite) {
      buildFile(dirPath, fullPath, contents);
    } else {
      console.log('Aborting');
      return false;
    }

    return true;
  };
}

function buildFile(dirPath, fullPath, contents) {
  console.log('Writing file...');

  fs.mkdirSync(dirPath, { recursive: true });
  fs.writeFileSync(fullPath, contents);

  console.log('SUCCESS\n');
}

export const strictYes = 'YES';
export const strictTrue = 'TRUE';

export const strictNo = 'NO';
export const strictFalse = 'FALSE';

export const truthyOptions = [
  strictTrue,
  strictTrue.toLowerCase(),

  strictYes,
  strictYes.toLowerCase(),

  strictYes[0],
  strictYes[0].toLowerCase(),
];

export const falseyOptions = [
  strictFalse,
  strictFalse.toLowerCase(),

  strictNo,
  strictNo.toLowerCase(),

  strictNo[0],
  strictNo[0].toLowerCase(),
];

export const acceptedOptions = [...falseyOptions, ...truthyOptions];

export class ChatTree {
  static end = Symbol('end');

  getDefaultNode() {
    return {
      defaultAnswer: '',
      do: null,
      prompt: ({ responses, defaultAnswer }) =>
        `Please enter your response (default: '${defaultAnswer})': `,
      formatAnswer: ({ userAnswer, defaultAnswer, responses }) =>
        userAnswer || defaultAnswer,
      addResponse: ({ answer, responses }) =>
        answer !== ''
          ? { ...responses, [this.currentStep]: answer }
          : responses,
      navigateTo: async ({ answer, responses }) => {
        const nextRequiredResponse = await this.getNextRequiredResponse();
        if (nextRequiredResponse) return nextRequiredResponse;

        return ChatTree.end;
      },
    };
  }

  responses = null;
  requiredResponses = null;
  currentStep = null;

  tree = {};

  constructor(rl, { initialStep, responses, requiredResponses, tree }) {
    this.rl = rl;
    this.responses = responses;
    this.requiredResponses = requiredResponses;
    this.tree = tree;
    this.currentStep = initialStep;
  }

  setResponses(responses) {
    this.responses = responses;
  }

  async getNextRequiredResponse() {
    return this.requiredResponses.find(
      (resp) => !Object.hasOwn(this.responses, resp)
    );
  }

  async start() {
    if (!this.currentStep)
      this.currentStep = await this.getNextRequiredResponse();
    return this.next(this.currentStep);
  }

  async next(step) {
    this.currentStep = step;

    if (this.currentStep === ChatTree.end) {
      return this.responses;
    }

    const stepConfig = {
      ...this.getDefaultNode(),
      ...this.tree[this.currentStep],
    };

    if (typeof stepConfig.defaultAnswer === 'function') {
      stepConfig.defaultAnswer = stepConfig.defaultAnswer({
        responses: this.responses,
      });
    }

    const nextStep = await new Promise(async (resolve) => {
      if (stepConfig.do) {
        const doResult = await stepConfig.do({ responses: this.responses });

        return resolve(
          ['symbol', 'string'].includes(typeof stepConfig.navigateTo)
            ? stepConfig.navigateTo
            : stepConfig.navigateTo({
                doResult,
                requiredResponses: this.requiredResponses,
                responses: this.responses,
              })
        );
      }

      this.rl.question(
        stepConfig.prompt({
          responses: this.responses,
          defaultAnswer: stepConfig.defaultAnswer,
        }),
        async (userAnswer) => {
          const answer = await stepConfig.formatAnswer({
            responses: this.responses,
            userAnswer,
            defaultAnswer: stepConfig.defaultAnswer,
          });

          const newResponses = await stepConfig.addResponse({
            answer,
            responses: this.responses,
          });

          this.setResponses(newResponses);

          return resolve(
            ['symbol', 'string'].includes(typeof stepConfig.navigateTo)
              ? stepConfig.navigateTo
              : stepConfig.navigateTo({
                  answer,
                  requiredResponses: this.requiredResponses,
                  responses: this.responses,
                })
          );
        }
      );
    });

    return this.next(nextStep);
  }
}

export const doBlockSupportsChat = (rl) => {
  const arrayFormatter = ({ userAnswer, defaultAnswer }) => {
    let answer = userAnswer.length ? userAnswer : defaultAnswer;
    if (typeof answer === 'boolean') return answer;
    if (truthyOptions.includes(answer)) return true;
    if (falseyOptions.includes(answer)) return false;

    try {
      const response = JSON.parse(answer);
      return response;
    } catch (e) {
      console.error('oops! there was an error parsing your response', e);
    }
  };
  const boolFormatter = ({ userAnswer, defaultAnswer }) => {
    let answer = userAnswer.length ? userAnswer : defaultAnswer;
    if (typeof answer === 'boolean') return answer;
    if (truthyOptions.includes(answer)) return true;
    if (falseyOptions.includes(answer)) return false;
    return defaultAnswer;
  };

  const blockSupportChat = new ChatTree(rl, {
    initialStep: 'align',
    requiredResponses: [],
    responses: {
      className: false,
    },
    tree: {
      align: {
        defaultAnswer: false,
        prompt: ({ defaultAnswer }) => `
          Support Alignment?
          Supported options: true | false | array of any ["left", "right", "full", "wide", "center"]
          Default: ${defaultAnswer}
        `,
        formatAnswer: arrayFormatter,
        navigateTo: 'anchor',
      },
      anchor: {
        defaultAnswer: true,
        prompt: ({ defaultAnswer }) => `
          Support Anchors?
          Supported options: true | false
          Default: ${defaultAnswer}
        `,
        formatAnswer: boolFormatter,
        navigateTo: 'background',
      },
      background: {
        defaultAnswer: false,
        prompt: ({ defaultAnswer }) => `
          Support Background Colors?
          Supported options: true | false
          Default: ${defaultAnswer}
        `,
        formatAnswer: boolFormatter,
        addResponse: ({ answer, responses }) =>
          answer !== ''
            ? {
                ...responses,
                color: {
                  ...responses?.color,
                  gradient: answer,
                  background: answer,
                },
              }
            : responses,
        navigateTo: 'text',
      },
      text: {
        defaultAnswer: false,
        prompt: ({ defaultAnswer }) => `
          Support Text Colors?
          Supported options: true | false
          Default: ${defaultAnswer}
        `,
        formatAnswer: boolFormatter,
        addResponse: ({ answer, responses }) =>
          answer !== ''
            ? {
                ...responses,
                color: {
                  ...responses?.color,
                  text: answer,
                },
              }
            : responses,
        navigateTo: 'padding',
      },
      padding: {
        defaultAnswer: '["top", "bottom"]',
        prompt: ({ defaultAnswer }) => `
          Support Padding?
          Supported options: true | false | array of any ["top", "bottom", "left", "right"]
          Default: ${defaultAnswer}
        `,
        formatAnswer: arrayFormatter,
        addResponse: ({ answer, responses }) =>
          answer !== ''
            ? {
                ...responses,
                spacing: {
                  ...responses?.spacing,
                  padding: answer,
                },
              }
            : responses,
        navigateTo: 'margin',
      },
      margin: {
        defaultAnswer: '["top", "bottom"]',
        prompt: ({ defaultAnswer }) => `
          Support Margin?
          Supported options: true | false | array of any ["top", "bottom", "left", "right"]
          Default: ${defaultAnswer}
        `,
        formatAnswer: arrayFormatter,
        addResponse: ({ answer, responses }) =>
          answer !== ''
            ? {
                ...responses,
                spacing: {
                  ...responses?.spacing,
                  margin: answer,
                },
              }
            : responses,
        navigateTo: ChatTree.end,
      },
    },
  });

  return blockSupportChat.start();
};

const buildParentFieldPrefix = (
  options = { parentField: '', indentation: '' }
) =>
  options.parentField
    ? `${options.indentation}[ Parent Field: ${options.parentField} ] `
    : '';

export const doSingleFieldChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    requiredResponses: ['label', 'name', 'type'],
    responses: {},
    tree: {
      label: {
        prompt: () => `        ${parentFieldPrefix}Enter field label: `,
      },
      name: {
        defaultAnswer: ({ responses }) =>
          responses.label.toLowerCase().replaceAll(' ', '_'),
        prompt: ({ defaultAnswer }) =>
          `        ${parentFieldPrefix}Enter field name (default: ${defaultAnswer}): `,
      },
      type: {
        prompt: () => `        ${parentFieldPrefix}Enter field type: `,
        navigateTo: ({ answer }) => {
          if (['group', 'repeater'].includes(answer)) {
            return 'sub_fields';
          } else if ('flexible_content' === answer) {
            return 'flexible_content';
          } else if ('select' === answer) {
            return 'select_choices';
          } else {
            return ChatTree.end;
          }
        },
      },
      flexible_content: {
        async do({ responses }) {
          const newFields = await doFlexibleContentBuilderChat(rl, {
            parentField: responses.label,
            indentation: `  ${options?.indentation || ''}`,
          });
          chatBot.setResponses({
            ...responses,
            layouts: newFields,
          });
        },
        navigateTo: ChatTree.end,
      },
      select_choices: {
        async do({ responses }) {
          const newFields = await doChoiceBuilderChat(rl, {
            parentField: responses.label,
            indentation: `  ${options?.indentation || ''}`,
          });
          chatBot.setResponses({
            ...responses,
            choices: newFields,
          });
        },
        navigateTo: ChatTree.end,
      },
      sub_fields: {
        async do({ responses }) {
          const newFields = await doFieldBuilderChat(rl, {
            parentField: responses.label,
            indentation: `  ${options?.indentation || ''}`,
          });
          chatBot.setResponses({
            ...responses,
            sub_fields: newFields,
          });
        },
        navigateTo: ChatTree.end,
      },
    },
  });

  return chatBot.start();
};

export const doSingleChoiceChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    requiredResponses: ['label', 'key'],
    responses: {},
    tree: {
      label: {
        prompt: () => `        ${parentFieldPrefix}Enter field label: `,
      },
      key: {
        defaultAnswer: ({ responses }) =>
          responses.label.toLowerCase().replaceAll(' ', '_'),
        prompt: ({ defaultAnswer }) =>
          `        ${parentFieldPrefix}Enter field key (default: ${defaultAnswer}): `,
      },
    },
  });

  return chatBot.start();
};

export const doSingleLayoutChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    requiredResponses: ['label', 'name', 'sub_fields'],
    responses: {
      display: 'block',
    },
    tree: {
      label: {
        prompt: () => `        ${parentFieldPrefix}Enter field label: `,
      },
      name: {
        defaultAnswer: ({ responses }) =>
          responses.label.toLowerCase().replaceAll(' ', '_'),
        prompt: ({ defaultAnswer }) =>
          `        ${parentFieldPrefix}Enter field name (default: ${defaultAnswer}): `,
      },
      sub_fields: {
        async do({ responses }) {
          const newFields = await doFieldBuilderChat(rl, {
            parentField: responses.label,
            indentation: `  ${options?.indentation || ''}`,
          });
          console.log({ newFields });
          chatBot.setResponses({
            ...responses,
            sub_fields: newFields,
          });
        },
        navigateTo: ChatTree.end,
      },
    },
  });

  return chatBot.start();
};

export const doChoiceBuilderChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    initialStep: 'new_field',
    requiredResponses: [],
    responses: {},
    tree: {
      new_field: {
        async do({ responses }) {
          const newField = await doSingleChoiceChat(rl, options);
          chatBot.setResponses({
            ...responses,
            [newField.key]: newField.label,
          });
        },
        navigateTo: 'keep_adding',
      },
      keep_adding: {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `
        ${parentFieldPrefix}Add another choice?: (default: ${defaultAnswer}) `,
        addResponse: ({ responses }) => responses,
        navigateTo({ answer }) {
          if (truthyOptions.includes(answer)) return 'new_field';
          return ChatTree.end;
        },
      },
    },
  });

  return chatBot.start();
};

export const doFlexibleContentBuilderChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    initialStep: 'new_field',
    requiredResponses: [],
    responses: {},
    tree: {
      new_field: {
        async do({ responses }) {
          const newField = await doSingleLayoutChat(rl, options);
          console.log({ responses });
          chatBot.setResponses({
            ...responses,
            [newField.name]: newField,
          });
          console.log({ responses: chatBot.responses });
        },
        navigateTo: 'keep_adding',
      },
      keep_adding: {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `
        ${parentFieldPrefix}Add another choice?: (default: ${defaultAnswer}) `,
        addResponse: ({ responses }) => responses,
        navigateTo({ answer }) {
          if (truthyOptions.includes(answer)) return 'new_field';
          return ChatTree.end;
        },
      },
    },
  });

  return chatBot.start();
};

export const doFieldBuilderChat = (rl, options) => {
  const parentFieldPrefix = buildParentFieldPrefix(options);
  const chatBot = new ChatTree(rl, {
    initialStep: 'new_field',
    requiredResponses: [],
    responses: [],
    tree: {
      new_field: {
        async do({ responses }) {
          const newField = await doSingleFieldChat(rl, options);
          chatBot.setResponses([...responses, newField]);
        },
        navigateTo: 'keep_adding',
      },
      keep_adding: {
        defaultAnswer: strictYes,
        prompt: ({ defaultAnswer }) => `
        ${parentFieldPrefix}Add another field?: (default: ${defaultAnswer}) `,
        addResponse: ({ responses }) => responses,
        navigateTo({ answer }) {
          if (truthyOptions.includes(answer)) return 'new_field';
          return ChatTree.end;
        },
      },
    },
  });

  return chatBot.start();
};

function makeFieldListReducer(accessorType = 'fields') {
  const rootFieldGetter = (fieldName) =>
    accessorType === 'fields'
      ? `fields.${fieldName}`
      : `post.meta('${fieldName}')`;

  return function fieldListReducer(
    builtContents,
    field,
    _currentIndex,
    _fullFieldsArray,
    fieldsPrefix
  ) {
    const fieldAccessor = !fieldsPrefix
      ? rootFieldGetter(field.name)
      : `${fieldsPrefix}['${[field.name]}']`;
    const fieldID = fieldAccessor
      .replaceAll('.', '__')
      .replaceAll("['", '__')
      .replaceAll("']", '');

    const groupReducer = (
      subFieldContents,
      subField,
      subCurrentIndex,
      subFieldsArray,
      subfields_prefix = fieldAccessor
    ) =>
      fieldListReducer(
        subFieldContents,
        subField,
        subCurrentIndex,
        subFieldsArray,
        subfields_prefix
      );

    const subFieldReducer =
      (rowName) =>
      (
        subFieldContents,
        subField,
        subCurrentIndex,
        subFieldsArray,
        subfields_prefix = rowName
      ) =>
        fieldListReducer(
          subFieldContents,
          subField,
          subCurrentIndex,
          subFieldsArray,
          subfields_prefix
        );

    if (['text', 'range'].includes(field.type)) {
      return `
        ${builtContents}
        <p>{{${fieldAccessor}}}</p>
      `;
    } else if (field.type === 'wysiwyg') {
      return `
        ${builtContents}
        {{${fieldAccessor}}}
      `;
    } else if (field.type === 'link') {
      return `
        ${builtContents}
        <a href="{{${fieldAccessor}.url}}" alt="{{${fieldAccessor}.alt}}">{{${fieldAccessor}.title}}</a>
      `;
    } else if (field.type === 'flexible_content') {
      return `
        ${builtContents}
        <div>
          <h3>Flexible Layouts: ${fieldAccessor}</h3>

          ${Object.values(field.layouts).map(
            (layout, index) => `
            {% ${
              index > 0 ? 'elseif' : 'if'
            } ${fieldAccessor}.acf_fc_layout == '${layout.name}' %}
              <article>
                <h4>Layout type: {{${fieldAccessor}.acf_fc_layout}}</h4>

                ${layout.sub_fields.reduce(groupReducer, '')}
              </article>
          `
          )}
          {% endif %}
        </div>
      `;
    } else if (field.type === 'gallery') {
      return `
        ${builtContents}

        {% for image in ${fieldAccessor} %}
          {% include '@partial/media-item.twig' with { image } only %}
        {% endfor %}
      `;
    } else if (field.type === 'image') {
      return `
        ${builtContents}

        {% include '@partial/media-item.twig' with { image: ${fieldAccessor} } only %}
      `;
    } else if (field.type === 'post_object') {
      return `
        ${builtContents}

        {% include '@partial/tease_switcher.twig' with { post: get_post(${fieldAccessor}) } only %} 
      `;
    } else if (field.type === 'relationship') {
      return `
        ${builtContents}

        {% include '@partial/post-repeater.twig' with { posts: ${fieldAccessor} } only %} 
      `;
    } else if (field.type === 'select') {
      return `
        ${builtContents}
        ${Object.entries(field.choices).map(
          ([choiceKey, choiceValue], index, fullLayoutsArray) => `
        {% ${index > 0 ? 'elseif' : 'if'} ${fieldAccessor} == '${choiceKey}' %}
          <h4>Select type: ${choiceValue}</h4>
        `
        )}
        {% endif %}
      `;
    } else if (field.type === 'repeater') {
      return `
        ${builtContents}
        {% for row in ${fieldAccessor} %}
          ${field.sub_fields.reduce(subFieldReducer('row'), '')}
        {% endfor %}
      `;
    } else if (field.type === 'group') {
      return `
        ${builtContents}
        <div>
          <h3>Field Group: ${field.name}</h3>

          ${field.sub_fields.reduce(groupReducer, '')}
        </div>
      `;
    } else if (field.type === 'true_false') {
      return `
        ${builtContents}
        <div>
          <input type="checkbox" id="${fieldID}" name="${field.name}" {{ ${fieldAccessor} ? 'checked' : '' }} />
          <label for="${fieldID}">${field.label}</label>
        </div>
      `;
    }

    return builtContents;
  };
}

export function buildFieldsHTML(fields, accessorType = 'fields') {
  const fullFieldsDef = replaceClonedFieldDefinitions(fields);
  const fieldListReducer = makeFieldListReducer(accessorType);

  return fullFieldsDef.reduce(fieldListReducer, '');
}
