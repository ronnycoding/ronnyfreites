<?php

namespace Wpe_Content_Engine\Helper\Search\Config;

use Wpe_Content_Engine\Helper\Acf_Support\Acf;
use Wpe_Content_Engine\Helper\String_Transformation;

use function AtlasSearch\Index\map_taxonomy_name;

abstract class Configurable {
	abstract public function get_config( array $existing_config ): array;

	/**
	 * @param string  $model_name The name of the model i.e. post page rabbit zombie.
	 * @param string  $field_name The name of the field i.e. title, content, acmField, acfField.
	 * @param array   $existing The existing search config for the selected model in the DB.
	 * @param boolean $has_sub_fields determines if the field has sub fields.
	 * @return array
	 */
	public function provide_config(
		string $model_name, string $field_name, array $existing, bool $has_sub_fields = false
	): array {
		$new_config = $this->generate_field_config( true, 1, $has_sub_fields );

		// If there is no config set, then return the newly generated config with default values.
		if ( ! isset( $existing[ $model_name ][ $field_name ] ) ) {
			return $new_config;
		}

		// This setting is not configurable so we should
		// always set it to avoid cache hits.
		$existing[ $model_name ][ $field_name ] ['has_sub_fields'] = $has_sub_fields;

		// Inherit any new config settings.
		return array_replace( $new_config, $existing[ $model_name ][ $field_name ] );
	}

	/**
	 * @param bool $searchable Set whether the field is searchable.
	 * @param int  $weight Set the weight of the search field.
	 * @param bool $has_sub_fields Determines if the field has sub fields.
	 * @return array
	 */
	public function generate_field_config( bool $searchable, int $weight, bool $has_sub_fields = false ): array {
		return array(
			'searchable'     => $searchable,
			'weight'         => $weight,
			'has_sub_fields' => $has_sub_fields,

		);
	}

	public function generate_taxonomies_config( $object_type, $existing_config, $search_config ) {
		$taxonomies = get_object_taxonomies( $object_type, 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( false === $taxonomy->show_ui ) {
				continue;
			}
			$search_config [ $object_type ][ map_taxonomy_name( $taxonomy->name ) . '.name' ] = $this->provide_config(
				$object_type,
				map_taxonomy_name( $taxonomy->name ) . '.name',
				$existing_config,
			);
		}

		return $search_config;
	}

	public function get_acf_search_config( string $model_name, array $existing_config, array $current_config ): array {
		$acf_field_groups = \acf_get_field_groups( array( 'post_type' => $model_name ) );
		$result           = array();

		foreach ( $acf_field_groups as $key => $acf_field_group ) {
			if ( empty( $acf_field_group ) || ! $acf_field_group['active'] ) {
				continue;
			}
			$acf_fields = acf_get_fields( $acf_field_group );

			$title = String_Transformation::camel_case( $acf_field_group['title'] );
			foreach ( $acf_fields as $acf_field ) {
				// Skip unsupported fields.
				if ( in_array( $acf_field['type'], Acf::ACF_UNSUPPORTED_TYPES, true ) ) {
					continue;
				}

				// TODO: check if field group is a nested object and perform this function recursively.
				$name              = String_Transformation::camel_case( $acf_field['name'], array( '_' ) );
				$field_name_nested = "{$title}.{$name}";

				// TODO: consider iterating over this block again for more nested search configs.
				$has_sub_fields               = in_array( $acf_field['type'], Acf::ACF_NESTED_TYPES, true );
				$result[ $field_name_nested ] = $this->provide_config(
					$model_name,
					$field_name_nested,
					$existing_config,
					$has_sub_fields
				);
			}
		}

		$current_model_config          = isset( $current_config [ $model_name ] ) ? $current_config [ $model_name ] : array();
		$current_config[ $model_name ] = array_merge( $current_model_config, $result );

		return $current_config;
	}
}
