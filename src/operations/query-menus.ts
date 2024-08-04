import * as Types from '../generated/gql-global';

export type GetMenusQueryVariables = Types.Exact<{ [key: string]: never; }>;


export type GetMenusQuery = { __typename: 'RootQuery', menus?: { __typename: 'RootQueryToMenuConnection', nodes: Array<{ __typename: 'Menu', name?: string | null, menuItems?: { __typename: 'MenuToMenuItemConnection', nodes: Array<{ __typename: 'MenuItem', uri?: string | null, url?: string | null, order?: number | null, label?: string | null }> } | null }> } | null, generalSettings?: { __typename: 'GeneralSettings', title?: string | null, url?: string | null, description?: string | null } | null };
