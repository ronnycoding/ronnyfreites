import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwind from '@astrojs/tailwind';

import vercel from '@astrojs/vercel/serverless';

import partytown from '@astrojs/partytown';

// https://astro.build/config
export default defineConfig({
    site: process.env.VERCEL_ENV === 'production' ? 'https://ronnyfreites.com' : 'http://localhost:4321',

    integrations: [
        sitemap(),
        tailwind({
            applyBaseStyles: false
        }),
        partytown({
            forward: ['dataLayer.push'],
            debug: process.env.VERCEL_ENV !== 'production'
        })
    ],

    output: 'server',
    adapter: vercel({
        edgeMiddleware: true
    })
});
