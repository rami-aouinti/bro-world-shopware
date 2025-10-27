# Theme activation in Shopware 6.7

When a custom theme is installed but does not appear in the Shopware administration, run through the following checklist and activation steps.

## 1. Plugin essentials

1. **Plugin class** – Ensure the plugin class extends `Shopware\\Core\\Framework\\Plugin` and implements `Shopware\\Storefront\\Framework\\ThemeInterface`.
2. **`theme.json`** – Provide a valid JSON file at `src/Resources/theme.json` with at least `name`, `label`, `author`, `views`, and `assetPaths`.
3. **`composer.json` metadata** – Declare the plugin type and class:
   ```json
   {
       "type": "shopware-platform-plugin",
       "extra": {
           "shopware-plugin-class": "BroWorldTheme\\\BroWorldTheme"
       }
   }
   ```
   Regenerate the autoloader after changes with `composer dump-autoload` inside the Shopware runtime container.

## 2. Refresh plugin and theme registries

```bash
bin/console plugin:refresh
bin/console plugin:install --activate BroWorldTheme
bin/console theme:refresh
```

If the CLI environment is not ready yet (for example PHP extensions are missing), prepare it first using the official Shopware development container or a local PHP 8.2/8.3 environment with the required extensions.

## 3. Assign the theme to a sales channel

List all sales channels to find the identifier:

```bash
bin/console sales-channel:list
```

List the registered themes to confirm the technical name that Shopware knows about:

```bash
bin/console theme:list
```

The `name` column corresponds to the value from `theme.json` (for example `BroWorldTheme`). Use that exact identifier when changing a theme. You can either pass both arguments directly or let the command prompt you for the sales channel:

```bash
# Option A: specify both arguments explicitly
bin/console theme:change BroWorldTheme 019a2575daf672219cf6d0879f247572

# Option B: provide only the theme and pick the channel interactively
bin/console theme:change BroWorldTheme
```

If the interactive flow ends with `Invalid theme name`, the theme has not been registered correctly—double-check the `name` in `theme.json`, rerun `bin/console theme:refresh`, and repeat `theme:list` to ensure the theme appears before retrying `theme:change`.

## 4. Compile storefront assets

Generate the storefront assets after assignment so that SCSS/JS changes become available:

```bash
bin/console theme:compile --active-only
```

Finally, clear the cache and reload the administration (`Content > Themes`). The custom theme should now be listed and selectable.
