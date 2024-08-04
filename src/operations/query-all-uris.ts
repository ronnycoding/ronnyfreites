import * as Types from '../generated/gql-global';

export type GetAllUrisQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type GetAllUrisQuery = { __typename: 'RootQuery', terms?: { __typename: 'RootQueryToTermNodeConnection', nodes: Array<{ __typename: 'Category', uri?: string | null } | { __typename: 'PostFormat', uri?: string | null } | { __typename: 'Tag', uri?: string | null }> } | null, posts?: { __typename: 'RootQueryToPostConnection', nodes: Array<{ __typename: 'Post', uri?: string | null }> } | null, pages?: { __typename: 'RootQueryToPageConnection', nodes: Array<{ __typename: 'Page', uri?: string | null }> } | null };
