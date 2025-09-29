// EXAMPLE:
import fieldsBodyCopy from '../lib/acf-composer/fields/body-copy.json' with {type: 'json'};
import fieldsThemeColor from '../lib/acf-composer/fields/theme-color.json' with {type: 'json'};
import fieldsFullWysiwyg from '../lib/acf-composer/fields/full-wysiwyg.json' with {type: 'json'};
import fieldsImage from '../lib/acf-composer/fields/image.json' with {type: 'json'};
import fieldsPhone from '../lib/acf-composer/fields/phone.json' with {type: 'json'};
import fieldsSingleLineBasicWysiwyg from '../lib/acf-composer/fields/single-line-basic-wysiwyg.json' with {type: 'json'};

const replacement_prefix = "LimeRockTheme/ACF";

const replacement_map = {
  // EXAMPLE:
  [`${replacement_prefix}/fields/body-copy`]: fieldsBodyCopy,
  [`${replacement_prefix}/fields/theme-color.json`]: fieldsThemeColor,
  [`${replacement_prefix}/fields/full-wysiwyg.json`]: fieldsFullWysiwyg,
  [`${replacement_prefix}/fields/image.json`]: fieldsImage,
  [`${replacement_prefix}/fields/phone.json`]: fieldsPhone,
  [`${replacement_prefix}/fields/single-line-basic-wysiwyg.json`]: fieldsSingleLineBasicWysiwyg,
}

export function isComposedField(fieldDef) {
  return typeof fieldDef === 'string' || (typeof fieldDef === 'object' && !!fieldDef.acf_composer_extend);
}

export function replaceComposedField(fieldFilter) {
  const isStr = typeof fieldFilter === 'string';
  const { acf_composer_extend, ...other_fields } = isStr ? {acf_composer_extend: fieldFilter} : fieldFilter;

  const base_fields = replacement_map[acf_composer_extend.split('#')[0]];

  return {
    ...base_fields,
    ...other_fields
  };
}
