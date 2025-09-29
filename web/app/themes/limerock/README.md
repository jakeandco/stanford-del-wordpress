# Jake & Co. Base Theme

Based off of the [Timber Starter Theme](https://github.com/timber/starter-theme).

## Installing the theme

Follow the guide on [how to Install Timber using the Starter Theme](https://timber.github.io/docs/v2/installation/installation/#use-the-starter-theme).

Then,

1. Rename the theme folder and any references to "LimeRockTheme" to something that makes sense for the site.
2. Activate the theme in the WordPress Dashboard under **Appearance → Themes**.
3. Update Bootstrap variables after reading [the docs](https://getbootstrap.com/docs/5.3/getting-started/introduction/).
4. Do your thing! And read [the Timber docs](https://timber.github.io/docs/).

## The `LimeRockTheme` class

In **functions.php**, we call `new LimeRockTheme();`. The `LimeRockTheme` class sits in the **lib** folder. You can update this class to add functionality to your theme. This approach is just one example for how you could do it.

The **lib** folder would be the right place to put your classes that [extend Timber’s functionality](https://timber.github.io/docs/v2/guides/extending-timber/).

## What else is there?

- `lib/` is where backend files can be found.
  - `lib/acf-composer` is where new generic fields can be located for inclusion in a block's `acf-composed.json` (See Block Development below)
- `static/` is where you can keep your _**-static-**_ front-end scripts, styles, or images. This should be hardly used
- `src/` is where you can keep your _**-dynamic-**_ front-end scripts, styles, or images. This is where almost all JS, SCSS, and assets should live.
- `views/` contains all of your Twig templates. These pretty much correspond 1 to 1 with the PHP files that respond to the WordPress template hierarchy. At the end of each PHP template, you’ll notice a `Timber::render()` function whose first parameter is the Twig file where that data (or `$context`) will be used. Just an FYI.
  - `views/blocks/` Will contain blocks you can include in the Gutenberg editor.
    - To create new blocks: See Scaffolding
    - For general block dev: See Block Development
- `skel/` Contains the source code for our scaffolding tool (See Scaffolding below)
- `acf-json/` Contains ACF field, post type, and options definitions. These can be overwritten or edited in the ACF tab of the WordPress Admin
- `.storybook/` Contains everything needed to get Storybook up and running. (See Storybook below)
- `tests/` ... basically don’t worry about (or remove) this unless you know what it is and want to.

## Scaffolding

An npm script has been added to help scaffold out various elements of our WordPress site.

Check out `/skel` for the source code, and feel free to modify or add to the commands available.

### Usage

```sh
npm run generate
```

A few commands and an example for each are listed below, but in general specific commands can be called
in the following format.

```sh
npm run generate <command> -- --<var name>=<var value>
```

Check out the available variables for each command by running

```sh
npm run generate <command> -- --help
```

However, don't worry about passing in every variable listed. If the script detects a variable is
missing, a prompt will ask you to fill it in, with sensible defaults for most of them.

#### Blocks

```sh
npm run generate block -- --name="My Block"
```

#### Post Types

```sh
npm run generate post-type -- --name="My Post Type"
```

#### Options Pages

```sh
npm run generate options -- --name="My Options Page"
```

## Block Development

When a block includes an `acf-composed.json` file, common fields (as defined in `lib/acf-composer/<type>/<field>.json`) may be included in the `fields` array.

```JSON
{
  "name": "group_block_my-block",
  "title": "Block Details: My Block",
  "fields": [
    "LimeRockTheme/ACF/fields/body-copy", // include a field without overrides
    {
      "acf_composer_extend": "LimeRockTheme/ACF/fields/body-copy" // include a field with overrides
      "name": "overridden",
      "label": "Overriden Field"
    }
    { // make our own unique field
        "name": "repeater",
        "label": "Uncomposed Field (Repeater)",
        "type": "repeater",
        "sub_fields": [
          { // but include a field within it's sub_fields
            "acf_composer_extend": "LimeRockTheme/ACF/fields/body-copy"
            "name": "nested-field",
            "label": "Nested Field"
          }
        ]
    }
  ],
  // etc...
}
```

See [ACF Field Group Composer](https://github.com/jakeandco/acf-field-group-composer) for more details.

## Storybook

[Storybook](https://storybook.js.org/docs) is supported by default for block development in this project.

Check it out by running `npm run storybook`

### Post Type support

To add better support for a specific post type, add a new key to the `by_post_type` object in `.storybook/acf-helpers/post_types.js`

Make sure to spread the `postTypeDefaults` so that generic keys don't have to be redefined every time.

An example can be found just below where the variable is initialized.

#### Post Meta Fields / ACF Fields

ACF fields that are attached to a post type can be supported by adding keys to the `meta_fields` object in the following format

```JSON
{
  meta_fields: {
     <field slug>: <example value>,
  }
}
```

#### Taxonomies

Terms (like `category` or `tags`) can be added by adding a new key to the `terms_mapping` object in the following format:

```JSON
    <taxonomy slug> : [
      { name: "<term name>", slug: "<term slug>" },
      { name: "<term name>", slug: "<term slug>" },
      ...etc
    ]
```
