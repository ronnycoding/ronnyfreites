# WordPress CLI Commands and Plugin Configuration

This document outlines various useful WordPress CLI commands for managing plugins, themes, and other WordPress functionalities within a Docker environment. It also includes information on custom plugin setup for ACF blocks and form submissions.

## Table of Contents

- [WordPress CLI Commands and Plugin Configuration](#wordpress-cli-commands-and-plugin-configuration)
  - [Table of Contents](#table-of-contents)
  - [Plugin Management](#plugin-management)
  - [Theme Creation](#theme-creation)
  - [Installing Essential Plugins](#installing-essential-plugins)
  - [Custom ACF Block Registration](#custom-acf-block-registration)
  - [Contact Form 7 Configuration with JWT Authentication](#contact-form-7-configuration-with-jwt-authentication)
  - [Database Backup](#database-backup)

## Plugin Management

To install and activate a plugin:

```bash
docker exec wpcli wp plugin install [plugin-slug|zip-url] --activate
```

Replace `[plugin-slug|zip-url]` with either the plugin's slug from the WordPress repository or a direct URL to the plugin's zip file.

## Theme Creation

To create a new WordPress theme:

```bash
docker exec wpcli wp scaffold _s sample-theme --theme_name="Sample Theme" --author="John Doe"
```

This command creates a new theme based on the `_s` (Underscores) starter theme. Customize the `sample-theme`, `"Sample Theme"`, and `"John Doe"` parameters as needed.

## Installing Essential Plugins

Here's a list of recommended plugins along with their installation commands:

```bash
# GraphQL API for WordPress
docker exec wpcli wp plugin install wp-graphql --activate

# Headless Mode
docker exec wpcli wp plugin install headless-mode --activate

# Yoast SEO
docker exec wpcli wp plugin install wordpress-seo --activate

# Add WPGraphQL SEO
docker exec wpcli wp plugin install add-wpgraphql-seo --activate

# WPGraphQL Gutenberg
docker exec wpcli wp plugin install https://github.com/pristas-peter/wp-graphql-gutenberg/archive/refs/heads/develop.zip --activate

# WPGraphQL Content Blocks
docker exec wpcli wp plugin install https://github.com/wpengine/wp-graphql-content-blocks/releases/latest/download/wp-graphql-content-blocks.zip --activate

# Advanced Custom Fields Pro
docker exec wpcli wp plugin install https://github.com/wordpress-premium/advanced-custom-fields-pro/archive/refs/heads/master.zip  --activate

# WPGraphQL for ACF
docker exec wpcli wp plugin install wpgraphql-acf --activate

# Contact Form 7
docker exec wpcli wp plugin install contact-form-7 --activate

# JWT Authentication for WP REST API
docker exec wpcli wp plugin install jwt-authentication-for-wp-rest-api --activate

# Add CORS support
docker exec wpcli wp plugin install wp-cors --activate

# Total Counts for WPGraphQL (Nice to paginate queries)
docker exec wpcli wp plugin install git@github.com:builtbycactus/total-counts-for-wp-graphql.git --activate
```

## Custom ACF Block Registration

Instead of creating custom Gutenberg blocks, we'll use ACF to register blocks. Create a plugin named `register-acf-blocks` with the following structure:

```php
<?php
/**
 * Plugin Name: register-acf-blocks
 * Plugin URI: https://yourwebsite.com/
 * Description: This plugin is to register ACF blocks.
 * Version: 0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 **/

add_action("acf/init", "register_acf_blocks_init");
function register_acf_blocks_init() {
    if (function_exists("acf_register_block")) {
        // Register your blocks here
        acf_register_block([
            "name" => "example-block",
            "title" => __("Example Block"),
            "description" => __("A custom example block."),
            "render_callback" => "render_example_block",
            "category" => "theme",
            "icon" => "admin-comments",
        ]);
    }
}

function render_example_block($block) {
    // Render your block content here
}
```

Activate the plugin:

```bash
docker exec wpcli wp plugin activate register-acf-blocks
```

## Contact Form 7 Configuration with JWT Authentication

1. Create a new plugin `contact-form-7-capabilities`:

```php
<?php
/**
 * Plugin Name: Contact Form 7 Subscriber Role Capabilities
 * Plugin URI: https://yourwebsite.com/
 * Description: This plugin adds capabilities to the subscriber user role for Contact Form 7.
 * Version: 0.1
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 **/

add_filter("wpcf7_map_meta_cap", "custom_wpcf7_map_meta_cap", 20);

function custom_wpcf7_map_meta_cap($meta_caps) {
    // Allow subscribers to read contact forms and submit them
    $meta_caps["wpcf7_read_contact_forms"] = "read";
    $meta_caps["wpcf7_edit_contact_form"] = "read";
    $meta_caps["wpcf7_submit"] = "read";
    return $meta_caps;
}
```

2. Activate the plugin:

```bash
docker exec wpcli wp plugin activate contact-form-7-capabilities
```

3. Create a new user with a subscriber role:

```bash
docker exec wpcli wp user create form_submitter form_submitter@example.com --role=subscriber --user_pass=secure_password
```

4. Configure JWT Authentication:

Add the following to your `wp-config.php`:

```php
define('JWT_AUTH_SECRET_KEY', 'your-secret-key');
define('JWT_AUTH_CORS_ENABLE', true);
```

Make sure to replace 'your-secret-key' with a secure, unique key.

## Database Backup

To create a backup of your database:

```bash
docker exec wpcli wp db export /var/www/html/db-backups/backup-site.sql
```

This command will create a SQL dump of your database in the specified location.
