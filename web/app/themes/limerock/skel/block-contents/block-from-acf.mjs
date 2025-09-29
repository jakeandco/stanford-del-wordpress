import fs from "node:fs"
import { buildFieldsHTML } from '../skel-helpers.mjs';

export default function (fieldsDefPath) {
  const fileContents = fs.readFileSync(fieldsDefPath).toString();
  const fieldsJSON = JSON.parse(fileContents);
  return buildFieldsHTML(fieldsJSON.fields);
}