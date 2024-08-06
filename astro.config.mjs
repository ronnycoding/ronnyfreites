import { defineConfig } from "astro/config";

// import node from "@astrojs/node";
import vercel from "@astrojs/vercel/serverless";

// https://astro.build/config
import sitemap from "@astrojs/sitemap";

// https://astro.build/config
export default defineConfig({
  output: "server",
  adapter: vercel(),
  site: import.meta.env.SITE_URL,
  integrations: [sitemap()],
});
