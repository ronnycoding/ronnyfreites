# Dante - Astro & Tailwind CSS Theme with WordPress Integration

Dante is a single-author blog and portfolio theme for Astro.js, now featuring seamless integration with WordPress as a headless CMS. This theme combines a minimal, slick, responsive design with the power of WordPress content management and GraphQL API.

![Dante Astro.js Theme](public/dante-preview.jpg)

## Theme Features:

- ✅ All original Dante theme features (Dark/light mode, Hero section, Portfolio collection, Pagination, etc.)
- ✅ WordPress integration as a headless CMS
- ✅ GraphQL API support for efficient data fetching
- ✅ TypeScript integration for improved type safety
- ✅ Docker Compose setup for easy WordPress and WP-CLI configuration

## New Integrations

- @astrojs/vercel - for serverless deployment
- graphql-request - for making GraphQL queries to WordPress
- @graphql-codegen - for generating TypeScript types from GraphQL schema

## WordPress Plugins

The following WordPress plugins are required for full functionality:

- WPGraphQL
- WPGraphQL for ACF
- Advanced Custom Fields (ACF)

## Project Structure

The project structure remains largely the same, with additional files for GraphQL and TypeScript integration:

```text
├── ...
├── src/
│   ├── ...
│   ├── generated/   # Contains generated TypeScript types
│   ├── lib/         # GraphQL query functions
│   └── ...
├── wp/              # WordPress files and directories
├── codegen.ts       # GraphQL Code Generator configuration
├── docker-compose.yml # Docker Compose configuration
├── ...
```

## Setup Instructions

1. Make sure you have Docker and Docker Compose installed on your system.

2. Clone this repository and navigate to the project directory.

3. Start the WordPress and database containers:
   ```
   docker-compose up -d
   ```

4. Access the WordPress admin panel at `http://localhost:8086/wp-admin` and complete the installation process.

5. Install and activate required WordPress plugins using WP-CLI:
   ```
   docker-compose run --rm wpcli wp plugin install wp-graphql advanced-custom-fields wpgraphql-acf --activate
   ```

6. Configure your `.env` file with your WordPress GraphQL endpoint:
   ```
   WP_GRAPHQL_URL=http://localhost:8086/graphql
   ```

7. Install project dependencies:
   ```
   npm install
   ```

8. Generate TypeScript types from your GraphQL schema:
   ```
   npm run codegen
   ```

9. Start the Astro development server:
   ```
   npm run dev
   ```

## WP-CLI Usage

You can use WP-CLI to manage your WordPress installation. Here are some example commands:

- List installed plugins:
  ```
  docker-compose run --rm wpcli wp plugin list
  ```

- Update WordPress core:
  ```
  docker-compose run --rm wpcli wp core update
  ```

- Create a new post:
  ```
  docker-compose run --rm wpcli wp post create --post_type=post --post_title='Hello World' --post_status=publish
  ```

For more WP-CLI commands, refer to the [official WP-CLI documentation](https://developer.wordpress.org/cli/commands/).

## Astro.js Commands

All commands are run from the root of the project, from a terminal:

| Command                   | Action                                           |
| :------------------------ | :----------------------------------------------- |
| `npm install`             | Installs dependencies                            |
| `npm run dev`             | Starts local dev server at `localhost:4321`      |
| `npm run build`           | Build your production site to `./dist/`          |
| `npm run preview`         | Preview your build locally, before deploying     |
| `npm run astro ...`       | Run CLI commands like `astro add`, `astro check` |
| `npm run astro -- --help` | Get help using the Astro CLI                     |
| `npm run codegen`         | Generate TypeScript types from GraphQL schema    |

## Credits

- Original Dante theme design and implementation by [justgoodui.com](https://justgoodui.com/)
- WordPress headless CMS integration, GraphQL implementation, TypeScript setup, and Docker configuration by [ronnyfreites.com](https://ronnyfreites.com/)
- Images for demo content from [Unsplash](https://unsplash.com/)

## License

Licensed under the [GPL-3.0](https://github.com/RonnyFrayRegato/dante-astro-theme-wp/blob/main/LICENSE) license.
