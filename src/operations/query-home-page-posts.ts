import * as Types from '../generated/gql-global';

export type GetHomePagePostsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type GetHomePagePostsQuery = { __typename: 'RootQuery', posts?: { __typename: 'RootQueryToPostConnection', nodes: Array<{ __typename: 'Post', date?: string | null, uri?: string | null, title?: string | null, commentCount?: number | null, excerpt?: string | null, categories?: { __typename: 'PostToCategoryConnection', nodes: Array<{ __typename: 'Category', name?: string | null, uri?: string | null }> } | null, featuredImage?: { __typename: 'NodeWithFeaturedImageToMediaItemConnectionEdge', node: { __typename: 'MediaItem', srcSet?: string | null, sourceUrl?: string | null, altText?: string | null, mediaDetails?: { __typename: 'MediaDetails', height?: number | null, width?: number | null } | null } } | null }> } | null };
