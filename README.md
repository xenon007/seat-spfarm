# SP Farming Plugin for SeAT

This repository contains a private SeAT plugin that provides an SP Farming
dashboard and configuration interface. It allows users to select which of
their EVE Online characters are considered part of a skillpoint farm, to
enable or disable PI monitoring per character, and to supply free‑form
descriptions of their skill plans. The dashboard displays a summary of
current training activity, projected extraction dates and optional idle
monitoring for non‑farm characters.

## Features

- **Menu integration**: Adds an `SP Farming` entry to the SeAT sidebar with
  `Dashboard` and `Settings` sub‑items.
- **Farm roster management**: Users can mark any of their characters as part
  of the farm and toggle PI usage individually.
- **Plan descriptions**: An optional text field per character holds a list
  of skills or notes about the plan. This appears as a tooltip on the
  dashboard.
- **Idle monitor**: A global toggle shows or hides a secondary table of
  non‑farm characters along with their training status.
- **Graceful degradation**: All advanced columns (location, last online,
  extraction date, omega) fall back to sensible defaults (`unknown` or
  `NEED ATTENTION!`) when data is not available from ESI or SeAT.

## Installation

This plugin is designed for SeAT `^5.x` and requires PHP `^8.1`. It is
intended for private use and is **not published to Packagist**. To install:

1. Clone or extract this repository into your SeAT installation under
   `plugins/xenon007-seat-spfarm`.
2. From your SeAT directory, run:

   ```bash
   composer config repositories.xenon007-seat-spfarm path ./plugins/xenon007-seat-spfarm
   composer require xenon007/seat-spfarm:1.0.0
   php artisan migrate
   ```

3. Visit the SeAT control panel. A new `SP Farming` entry should appear in
   the sidebar.

Because this plugin relies on SeAT's dynamic relationships to retrieve
training data, some installations may not expose all columns. Values may
appear as `unknown` or `NEED ATTENTION!` until additional scopes are
authorised or SeAT data is refreshed.

## Configuration

The default route prefix is `spfarm`. If this conflicts with other routes
you may customise it by publishing the configuration file and editing
`config/seat-spfarm.php`:

```bash
php artisan vendor:publish --tag=seat-spfarm-config
```

## Support

This plugin is provided as‑is with no guarantee of support. It is
deliberately minimalist and may require further development for advanced
SeAT deployments. Feel free to fork and adapt it to your needs.