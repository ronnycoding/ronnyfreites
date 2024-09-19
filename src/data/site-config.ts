export type Image = {
    src: string;
    alt?: string;
    caption?: string;
};

export type Link = {
    text: string;
    href: string;
};

export type Hero = {
    title?: string;
    text?: string;
    image?: Image;
    actions?: Link[];
};

export type Subscribe = {
    title?: string;
    text?: string;
    formUrl: string;
};

export type SiteConfig = {
    logo?: Image;
    title: string;
    subtitle?: string;
    description: string;
    image?: Image;
    headerNavLinks?: Link[];
    footerNavLinks?: Link[];
    socialLinks?: Link[];
    hero?: Hero;
    subscribe?: Subscribe;
    postsPerPage?: number;
    projectsPerPage?: number;
};

const siteConfig: SiteConfig = {
    socialLinks: [
        {
            text: 'Threads',
            href: 'https://www.threads.net/@ronnycoding'
        },
        {
            text: 'X',
            href: 'https://x.com/ronnyfreites'
        },
        {
            text: 'GitHub',
            href: 'https://github.com/ronnycoding'
        },
        {
            text: 'LinkedIn',
            href: 'https://www.linkedin.com/in/ronnyfreites'
        }
    ]
};

export default siteConfig;
