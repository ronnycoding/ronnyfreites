import type {
    CoreCode,
    CoreHeading,
    CoreImage,
    CoreList,
    CoreListItem,
    CoreParagraph,
    CoreQuote,
    Page,
    Post,
    Category,
    CoreButtons,
    CoreButton,
    // ContactForm7ContactFormSelector,
    Menu,
    MenuItem,
    RootQueryToTagConnectionEdge,
    Tag
} from '@/gql/graphql';

export type WPBlock =
    | CoreParagraph
    | CoreHeading
    | CoreList
    | CoreListItem
    | CoreImage
    | CoreQuote
    | CoreCode
    // | ContactForm7ContactFormSelector
    | Page
    | Post
    | Category
    | CoreButtons
    | CoreButton
    | Menu
    | MenuItem
    | RootQueryToTagConnectionEdge
    | Tag;

export type {
    CoreCode,
    CoreHeading,
    CoreImage,
    CoreList,
    CoreListItem,
    CoreParagraph,
    CoreQuote,
    Page,
    Post,
    Category,
    CoreButtons,
    CoreButton,
    // ContactForm7ContactFormSelector,
    Menu,
    MenuItem,
    Tag,
    RootQueryToTagConnectionEdge
};

type WPBlockTypeKeys = NonNullable<WPBlock['__typename']>;

type WPBlockTypeMap = {
    [K in WPBlockTypeKeys]: Extract<WPBlock, { __typename?: K }>;
};

export const isWPBlock = (block: unknown): block is WPBlock => {
    return (
        block !== null &&
        typeof block === 'object' &&
        '__typename' in block &&
        typeof block.__typename === 'string' &&
        [
            'CoreParagraph',
            'CoreHeading',
            'CoreList',
            'CoreListItem',
            'CoreImage',
            'CoreQuote',
            'CoreCode',
            'CoreButtons',
            'CoreButton',
            'ContactForm7ContactFormSelector',
            'Page',
            'Post',
            'Category',
            'Menu',
            'MenuItem',
            'Tag',
            'RootQueryToTagConnectionEdge'
        ].includes(block.__typename)
    );
};

export function isGraphType<T extends WPBlockTypeKeys>(obj: unknown, typeName: T): obj is WPBlockTypeMap[T] {
    if (!isWPBlock(obj)) return false;
    return obj.__typename === typeName;
}
