<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Validator {

	public static function sanitize_field_id( mixed $id ): string {
		$clean = sanitize_text_field( (string) $id );
		return preg_match( '/^\d+(\.\d+)?$/', $clean ) ? $clean : '';
	}

	public static function sanitize_context_key( mixed $key ): string {
		$clean = sanitize_text_field( (string) $key );
		return preg_match( '/^\d+(_n_[a-zA-Z0-9]+)?$/', $clean ) ? $clean : '';
	}

	public static function sanitize_exclusions_array( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];

		foreach ( $input as $key => $fields ) {
			$clean_key = self::sanitize_context_key( $key );
			if ( empty( $clean_key ) ) {
				continue;
			}

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$clean_fields = [];
			foreach ( $fields as $field_id ) {
				$sanitized_id = self::sanitize_field_id( $field_id );
				if ( '' !== $sanitized_id ) {
					$clean_fields[] = $sanitized_id;
				}
			}

			$clean_fields = array_values( array_unique( $clean_fields ) );

			if ( ! empty( $clean_fields ) ) {
				$clean[ $clean_key ] = $clean_fields;
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