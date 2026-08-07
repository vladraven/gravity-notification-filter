<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Validator {

	public static function sanitize_exclusions_array( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];

		foreach ( $input as $form_id => $fields ) {
			$clean_form_id = absint( $form_id );
			if ( $clean_form_id <= 0 ) {
				continue;
			}

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$clean_fields = array_values( array_unique( array_map( 'absint', $fields ) ) );
			$clean_fields = array_filter( $clean_fields, static fn( $id ) => $id > 0 );

			if ( ! empty( $clean_fields ) ) {
				$clean[ $clean_form_id ] = $clean_fields;
			}
		}

		return $clean;
	}

	public static function validate_json_import( string $json_string ): array|WP_Error {
		if ( empty( $json_string ) ) {
			return new WP_Error( 'empty_json', __( 'Import payload is empty.', 'gravity-notification-filter' ) );
		}

		$decoded = json_decode( $json_string, true );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error( 'invalid_json', __( 'Invalid JSON string provided.', 'gravity-notification-filter' ) );
		}

		return self::sanitize_exclusions_array( $decoded );
	}
}