# TransBundle
Provides database-backed translation management with GUI.

# Installation

```bash
composer require mpom/trans-bundle
```

Add to your AppKernel.php:
```php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new TransBundle\TransBundle,
            // ...
        )
    }
```

# Configuration
Make sure that you have enabled translator service in your config.yml.
In your config.yml file add new options:
```yml
trans:
    locales: [en, de] # managed locales
    layout: AppBundle:Admin:Layout/translations.html.twig # optional, layout file
```

Add to app/config/routing.yml:
```yml
trans_gui:
    resource: "@TransBundle/Resources/config/routing.yml"
    prefix:   /
```

Import database structure:
```bash
console doctrine:schema:update --force
```

Dump assets:
```bash
console assets:install --symlink
```

# Usage
Navigate to /trans page.

Command line:
```bash
console trans:import # call to import translations from files to database
```

**Note:**
For every bundle and locale put enpty file into Resources/translations folder in format `<domain>.<locale>.orm`.
For example: instead of `messages.en.yml` keep `messages.en.orm`.

This bundle automatically adds untranslated strings to database at runtime.