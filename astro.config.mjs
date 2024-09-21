import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';

import vercel from '@astrojs/vercel/serverless';

// https://astro.build/config
export default defineConfig({
    site: process.env.VERCEL_ENV === 'production' ? 'https://ronnyfreites.com' : 'http://localhost:3000',

    integrations: [
        sitemap(),
        tailwind({
            applyBaseStyles: false
        })
    ],

    output: 'server',
    adapter: vercel({
        webAnalytics: {
            enabled: true
        },
        isr: {
            // caches all pages on first request and saves for 1 day
            expiration: 60 * 60 * 24
        },
        skewProtection: true
    })
});
