# Post Type Archive Settings

A lightweight WordPress plugin that adds editable title and description fields for post type archive pages. It also supports [Polylang](https://wordpress.org/plugins/polylang/) for multilingual sites.

## Installation

1. Upload the plugin folder to your `/wp-content/plugins/` directory or install it via the WordPress dashboard.
2. Activate **Post Type Archive Settings** from the Plugins screen.

## Usage

- After activation, a new **Archive Settings** submenu appears under each public post type in the dashboard.
- Set a custom archive title and description, optionally per language when Polylang is active.
- In your theme templates, output these values with:
  ```php
  mosne_pta_the_archive_title();
  mosne_pta_the_archive_description();
  ```
  Alternatively, retrieve them with `mosne_pta_get_archive_title()` and `mosne_pta_get_archive_description()`.

## Credits

Developed by [mosne](https://mosne.it).
