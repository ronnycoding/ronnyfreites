import * as Types from '../generated/gql-global';

export type GetAllSlugsQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type GetAllSlugsQuery = { __typename: 'RootQuery', terms?: { __typename: 'RootQueryToTermNodeConnection', nodes: Array<{ __typename: 'Category', slug?: string | null } | { __typename: 'PostFormat', slug?: string | null } | { __typename: 'Tag', slug?: string | null }> } | null, posts?: { __typename: 'RootQueryToPostConnection', nodes: Array<{ __typename: 'Post', slug?: string | null }> } | null, pages?: { __typename: 'RootQueryToPageConnection', nodes: Array<{ __typename: 'Page', slug?: string | null }> } | null };
