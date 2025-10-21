# Airtable Sync Plugin

A WordPress plugin that allows ongoing sync of content and metadata between Airtable bases and WordPress.

## Features

- Connect to Airtable using API key authentication
- Select Airtable bases from your account
- Map Airtable tables to WordPress post types
- Support for multiple table-to-post-type mappings

## Installation

1. Upload the `airtable-sync` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Airtable Sync' in the WordPress admin menu to configure settings

## Configuration

### API Key Setup

1. Navigate to **Airtable Sync** in the WordPress admin menu
2. Enter your Airtable API key (get it from [Airtable Account Settings](https://airtable.com/create/tokens))
3. Click "Save Settings"

### Base Selection

1. After saving your API key, click the "Load Bases" button
2. Select the Airtable base you want to sync from the dropdown
3. Click "Save Settings"

### Table Mappings

1. Click "Add Mapping" to create a new table-to-post-type mapping
2. Select an Airtable table from the dropdown (tables load automatically when you focus on the field)
3. Select the WordPress post type you want to map it to
4. Repeat for additional mappings as needed
5. Click "Save Settings"

## File Structure

```
airtable-sync/
├── airtable-sync.php           # Main plugin file
├── includes/
│   └── class-airtable-sync-admin.php  # Admin functionality
├── assets/
│   ├── css/
│   │   └── admin.css           # Admin styles
│   └── js/
│       └── admin.js            # Admin JavaScript
└── README.md                   # This file
```

## Developer Information

- **Developer:** Jake and Co.
- **Website:** https://jakeandco.com
- **Version:** 1.0.0

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Valid Airtable API key

## License

GPL v2 or later
