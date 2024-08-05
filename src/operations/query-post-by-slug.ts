import * as Types from '../generated/gql-global';

export type GetPostBySlugQueryVariables = Types.Exact<{
  slug: Types.Scalars['ID']['input'];
}>;


export type GetPostBySlugQuery = { __typename: 'RootQuery', post?: { __typename: 'Post', id: string, title?: string | null, date?: string | null, uri?: string | null, slug?: string | null, excerpt?: string | null, content?: string | null, categories?: { __typename: 'PostToCategoryConnection', nodes: Array<{ __typename: 'Category', name?: string | null, uri?: string | null }> } | null, featuredImage?: { __typename: 'NodeWithFeaturedImageToMediaItemConnectionEdge', node: { __typename: 'MediaItem', srcSet?: string | null, sourceUrl?: string | null, altText?: string | null, mediaDetails?: { __typename: 'MediaDetails', height?: number | null, width?: number | null } | null } } | null, seo?: { __typename: 'PostTypeSEO', fullHead?: string | null } | null } | null };
