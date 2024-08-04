import { GetHomePagePostsQuery } from "../operations/query-home-page-posts";
import { GetMenusQuery } from "../operations/query-menus";
import { GetNodeByUriQuery } from "../operations/query-node-by-uri";

const fetchQuery = async <T>(query, variables = {}): Promise<T> => {
  try {
    const response = await fetch(import.meta.env.WORDPRESS_API_URL, {
      method: "post",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        query: query,
        variables,
      }),
    });
    const { data } = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching data: ", error);
  }
};

export async function navQuery() {
  const res = await fetchQuery<GetMenusQuery>(
    `{
      menus(where: {location: PRIMARY}) {
        nodes {
          name
          menuItems {
              nodes {
                  uri
                  url
                  order
                  label
              }
          }
        }
      }
      generalSettings {
          title
          url
          description
      }
    }
  `
  );
  return res;
}

export async function homePagePostsQuery() {
  const res = await fetchQuery<GetHomePagePostsQuery>(
    `{
      posts {
        nodes {
          date
          uri
          title
          commentCount
          excerpt
          categories {
            nodes {
              name
              uri
            }
          }
          featuredImage {
            node {
              srcSet
              sourceUrl
              altText
              mediaDetails {
                height
                width
              }
            }
          }
        }
      }
    }
  `
  );
  return res;
}

export async function getNodeByURI(uri) {
  const res = await fetchQuery<GetNodeByUriQuery>(
    `{
      query GetNodeByURI($uri: String!) {
        nodeByUri(uri: $uri) {
          __typename
          isContentNode
          isTermNode
          ... on Post {
            id
            title
            date
            uri
            excerpt
            content
            categories {
              nodes {
                name
                uri
              }
            }
            featuredImage {
              node {
                srcSet
                sourceUrl
                altText
                mediaDetails {
                  height
                  width
                }
              }
            }
          }
          ... on Page {
            id
            title
            uri
            date
            content
          }
          ... on Category {
            id
            name
            posts {
              nodes {
                date
                title
                excerpt
                uri
                categories {
                  nodes {
                    name
                    uri
                  }
                }
                featuredImage {
                  node {
                    srcSet
                    sourceUrl
                    altText
                    mediaDetails {
                      height
                      width
                    }
                  }
                }
              }
            }
          }
        }
      }
  `,
    {
      uri: uri,
    }
  );

  return res;
}
