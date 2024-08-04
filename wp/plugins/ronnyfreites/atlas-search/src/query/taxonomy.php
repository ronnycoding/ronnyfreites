<?php

namespace AtlasSearch\Query\Taxonomy;

/**
 * This function returns a string filter that represents the tax_query.
 *
 * $sample_tax_query = array(
 *       array(
 *           'taxonomy' => 'category',
 *           'field'    => 'slug',
 *           'terms'    => array( 'uncategorized' ),
 *       ),
 *       array(
 *           'taxonomy' => 'tag',
 *           'field'    => 'slug',
 *           'terms'    => array( 'sport', 'rugby' ),
 *       ),
 *   );
 *
 * The above tax_query should return the following string:
 * categories.name:"uncategorized" AND tags.name:"sport","rugby"
 *
 * @param \WP_Query $wp_query Tax query.
 *
 * @return string|null
 */
function get_taxonomy_filter( \WP_Query $wp_query = null ) {
	if ( ! isset( $wp_query->tax_query ) ) {
		return null;
	}

	$output = '';
	/**
	 * Filter out the numeric keys from the tax_query.
	 * For some reason a key called "relation" makes it into
	 * the queries array. This key is not a query and should be ignored.
	 */
	$queries = array_filter(
		$wp_query->tax_query->queries,
		function ( $key ) {
			return is_numeric( $key );
		},
		ARRAY_FILTER_USE_KEY
	);

	foreach ( $queries as $query ) {
		if ( ! empty( $output ) ) {
			$output .= ' ' . $wp_query->tax_query->relation . ' ';
		}

		// handle taxonomy query field
		// category is indexed as categories and post_tag is indexed as tags.
		$tax_name = get_taxonomy_name( $query['taxonomy'] );

		// handle taxonomy query operator.
		$operator = isset( $query['operator'] ) ? $query['operator'] : 'IN';
		switch ( $operator ) {
			case 'NOT IN':
			case 'NOT EXISTS':
				$quote    = 'NOT IN' === $operator ? '"' : '';
				$template = 'NOT ' . $tax_name . '.' . $query['field'] . ':';
				$terms    = array_map(
					function( $term ) use ( $template, $quote ) {
						return $template . $quote . $term . $quote;
					},
					$query['terms']
				);
				$output  .= implode( ' ', $terms );
				break;
			case 'IN':
			case 'EXISTS':
			default:
				$quote   = 'IN' === $operator ? '"' : '';
				$output .= $tax_name . '.' . $query['field'] . ':';
				$output .= $quote . implode( "$quote,$quote", $query['terms'] ) . $quote;
				break;
		}
	}

	return $output;

}

/**
 * This function is used to map the taxonomy name to the fields in the index.
 *
 * @param string $taxonomy Taxonomy name.
 * @return string
 */
function get_taxonomy_name( $taxonomy ) {
	if ( 'category' === $taxonomy ) {
		return 'categories';
	} elseif ( 'post_tag' === $taxonomy ) {
		return 'tags';
	}

	return $taxonomy;
}
