import * as Types from '../generated/gql-global';

export type GetPageBySlugQueryVariables = Types.Exact<{
  slug: Types.Scalars['ID']['input'];
}>;


export type GetPageBySlugQuery = { __typename: 'RootQuery', page?: { __typename: 'Page', id: string, title?: string | null, date?: string | null, uri?: string | null, slug?: string | null, content?: string | null, featuredImage?: { __typename: 'NodeWithFeaturedImageToMediaItemConnectionEdge', node: { __typename: 'MediaItem', srcSet?: string | null, sourceUrl?: string | null, altText?: string | null, mediaDetails?: { __typename: 'MediaDetails', height?: number | null, width?: number | null } | null } } | null, seo?: { __typename: 'PostTypeSEO', fullHead?: string | null } | null } | null };
