# Demo Plugin

## Local Dev Environment

To get started, copy `.env.template` to `.env` and update any values.

Then run `init.sh` to download WordPress and stage the plugin files.

Note: Curl is required (git bash will likely work on Windows).

## Dev-Utils

The `dev-utils` folder is not checked into version control. Example commands in this documentation expects
following `.phar` files to be present:

- `wp-cli.phar`
- `composer.phar`

If you have those files installed somewhere else, or installed globally, it should be straightforward to modify the
provided commands for your dev environment.

## Tests

```bash
# from within the ./jds-demo-plugin directory
cd jds-demo-plugin
php vendor/bin/codecept run
```

## Updating the .pot file

```bash
# from within the ./jds-demo-plugin directory
cd jds-demo-plugin

# below parses twig templates and creates a dummy file with translated strings
php tasks/extract-twig-text.php

# run wp-cli to create/update the .pot file
php ../dev-utils/wp-cli.phar i18n make-pot . --ignore-domain languages/jds-demo-plugin.pot --path=../wordpress --include=cache/gettext/*.php --exclude=tests/* --merge
```
