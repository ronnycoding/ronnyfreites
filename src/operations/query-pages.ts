import * as Types from '../generated/gql-global';

export type GetPagesQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type GetPagesQuery = { __typename: 'RootQuery', pages?: { __typename: 'RootQueryToPageConnection', nodes: Array<{ __typename: 'Page', id: string, title?: string | null, slug?: string | null, content?: string | null, featuredImage?: { __typename: 'NodeWithFeaturedImageToMediaItemConnectionEdge', node: { __typename: 'MediaItem', srcSet?: string | null, sourceUrl?: string | null, altText?: string | null, mediaDetails?: { __typename: 'MediaDetails', height?: number | null, width?: number | null } | null } } | null, seo?: { __typename: 'PostTypeSEO', fullHead?: string | null } | null }> } | null };
