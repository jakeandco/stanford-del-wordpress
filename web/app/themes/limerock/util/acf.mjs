import {isComposedField, replaceComposedField} from './composed_fields.mjs';

export function replaceClonedFieldDefinitions(fieldDefinitionsList) {
  const deCloneReducer = (fullList, fieldDef) => {
    if (isComposedField(fieldDef)) {
      const replacedField = replaceComposedField(fieldDef)
  
      if (replacedField) {
        return deCloneReducer(fullList, replacedField);
      }
    }


    if (fieldDef.type === 'clone') {
      const replacementName = fieldDef.clone[0];
      const replacement = ACF_FIELDS[replacementName];
      if (replacement) {
        return [...fullList, ...replacement.fields];
      } else {
        return fullList;
      }
    }
    if (fieldDef.type === 'repeater' || fieldDef.type === 'group') {
      return [
        ...fullList,
        {
          ...fieldDef,
          sub_fields: replaceClonedFieldDefinitions(fieldDef.sub_fields),
        },
      ];
    }
    return [...fullList, fieldDef];
  }

  const deCloned = fieldDefinitionsList.reduce(deCloneReducer, []);

  return deCloned;
}
