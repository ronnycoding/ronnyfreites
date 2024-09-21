import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';

import vercel from '@astrojs/vercel/serverless';

// https://astro.build/config
export default defineConfig({
    site: 'https://ronnyfreites.com',
    // site: 'http://localhost:4321',

    integrations: [
        sitemap(),
        tailwind({
            applyBaseStyles: false
        })
    ],

    output: 'server',
    adapter: vercel()
});
