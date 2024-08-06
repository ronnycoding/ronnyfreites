export const stripDomain = (uri: string) => {
  return uri.replace(/^https?:\/\/[^\/]+/, "");
};

export const stripPostSlug = (uri: string) => {
  return uri.replace(/\/[^\/]+$/, "");
};
