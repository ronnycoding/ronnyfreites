# Smart Search

## Hooks and Filters

### `wpe_smartsearch/extra_fields`

Filters the post fields before indexing.

```php
apply_filters( 'wpe_smartsearch/extra_fields', string $data, WP_Post $post )
```

#### Description

Use this filter to add or remove fields before an object is indexed.

#### Parameters

```php
$data array
```

Contains all of the data before indexing.

```php
$post WP_Post
```

The current post object being indexed.

#### Examples

Adding a custom field.

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        $data['custom-field'] = 'custom field test';
        return $data;
    },
    10,
    2
);
```

Adding the post permalink.

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        //sample value http://localhost:8000/hello-world
        $data['url'] = get_permalink($post);
        return $data;
    },
    10,
    2
);
```

Adding the post locale with a language plugin, i.e. `polylang`

```php
add_filter(
    'wpe_smartsearch/extra_fields',
    function( array $data, WP_Post $post ) {
        //sample valus EN | ES
        $data['locale'] = pll_get_post_language($post->ID);
        return $data;
    },
    10,
    2
);
```


### `wpe_smartsearch/extra_search_config_fields`

Filters the search config fields.

```php
apply_filters( 'wpe_smartsearch/extra_search_config_fields', array $fields, string $post_type )
```

#### Description

Use this filter to add or remove fields to the Search Config.

#### Parameters

```php
$fields array
```

Contains all of the search config fields for the given `$post_type`.

```php
$post WP_Post
```

The current post_type being processed for Search Config fields.

#### Examples

Adding a custom search config field.


```php
add_filter(
    'wpe_smartsearch/extra_search_config_fields',
    function ( $fields, $post_type ) {
        if ($post_type === 'post' ) {
            $fields[] = 'my-custom-field';
        }

        return $fields;
    },
    10,
    2
);

```

**NOTE:** this hook will be ran multiple times for each post type that exists.

The following search config field will get added to all post types.

```php
add_filter(
    'wpe_smartsearch/extra_search_config_fields',
    function ( $fields, $post_type ) {
        $fields[] = 'all-post-types-custom-field';
        return $fields;
    },
    10,
    2
);

```


### `wpe_smartsearch/excluded_post_types`

Filters the post types ban list.

```php
apply_filters( 'wpe_smartsearch/excluded_post_types' );
```

#### Description

Filters a list of post types that won't be considered for WP Engine Smart Search

#### Examples

```php
add_filter(
    'wpe_smartsearch/excluded_post_types',
    function (  ) {
        return array(
            'zombie',
            'rabbit',
            'page'
        );
    },
    10,
    2
);
```

### `wpe_smartsearch/acf/excluded_field_names`

Filter ACF fields from being indexed to WPE Engine Smart Search.

```php
$excluded_field_names = apply_filters( 'wpe_smartsearch/acf/excluded_field_names', array() );
```

#### Description

This filter prevents ACF fields to be indexed using the field name. This is very useful for a number of reasons:
*  Preventing unnecessary data from being indexed, increases performance.
*  Prevents errors from being thrown when indexing data ( Errors like: Limit of total fields [1000] has been exceeded )

#### Examples
You would want to prevent ACF fields with names 'acf_field_name1', 'acf_field_name2', 'acf_field_name3' are not indexed.  

```php
add_filter( 'wpe_smartsearch/acf/excluded_field_names', function ( $excluded_field_names ) {
    $custom_excluded_field_names= array(
       'acf_field_name1',
       'acf_field_name2',
       'acf_field_name3',
    );

    return array_merge($excluded_field_names,$custom_excluded_field_names );
   },
 10,
 1
);
```