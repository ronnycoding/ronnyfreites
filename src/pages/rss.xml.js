import rss from '@astrojs/rss';
import { getRssPosts } from '@/lib/api';

export async function GET() {
    const rssData = await getRssPosts();
    const posts = rssData?.posts;
    return rss({
        title: rssData?.generalSettings?.title,
        description: rssData?.generalSettings?.description,
        site: rssData?.generalSettings?.url,
        items: posts?.edges?.map((post) => ({
            title: post.node.title,
            description: post.node.excerpt,
            pubDate: post.node.date,
            content: post.node.content,
            link: `https://ronnyfreites.com${post.node.uri}`,
            author: post.node.author.node.name
        }))
    });
}
