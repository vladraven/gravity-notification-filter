<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Validator {

	public static function sanitize_field_id( mixed $id ): string {
		if ( ! is_scalar( $id ) ) {
			return '';
		}

		$clean = sanitize_text_field( (string) $id );

		return preg_match(
			'/^\d+(?:\.\d+)?$/',
			$clean
		) ? $clean : '';
	}

	public static function sanitize_notification_id( mixed $id ): string {
		if ( ! is_scalar( $id ) ) {
			return '';
		}

		$clean = sanitize_text_field( (string) $id );

		return preg_match(
			'/^[a-zA-Z0-9_-]+$/',
			$clean
		) ? $clean : '';
	}

	public static function sanitize_context_key( mixed $key ): string {
		if ( ! is_scalar( $key ) ) {
			return '';
		}

		$clean = sanitize_text_field( (string) $key );

		if ( preg_match( '/^\d+$/', $clean ) ) {
			return $clean;
		}

		if (
			preg_match(
				'/^(\d+)_n_([a-zA-Z0-9_-]+)$/',
				$clean
			)
		) {
			return $clean;
		}

		return '';
	}

	public static function sanitize_exclusions_array(
		mixed $input
	): array {
		if ( ! is_array( $input ) ) {
			return [];
		}

		$clean = [];

		foreach ( $input as $key => $fields ) {
			$clean_key =
				self::sanitize_context_key( $key );

			if (
				'' === $clean_key
				|| ! is_array( $fields )
			) {
				continue;
			}

			$clean_fields = [];

			foreach ( $fields as $field_id ) {
				$sanitized_id =
					self::sanitize_field_id(
						$field_id
					);

				if ( '' !== $sanitized_id ) {
					$clean_fields[] =
						$sanitized_id;
				}
			}

			$clean_fields =
				array_values(
					array_unique(
						$clean_fields
					)
				);

			if ( ! empty( $clean_fields ) ) {
				$clean[ $clean_key ] =
					$clean_fields;
			}
		}

		return $clean;
	}

	public static function validate_json_import(
		string $json_string
	): array|WP_Error {
		if ( '' === trim( $json_string ) ) {
			return new WP_Error(
				'empty_json',
				__(
					'Import payload is empty.',
					'gravity-notification-filter'
				)
			);
		}

		try {
			$decoded = json_decode(
				$json_string,
				true,
				512,
				JSON_THROW_ON_ERROR
			);
		} catch ( JsonException $exception ) {
			return new WP_Error(
				'invalid_json',
				__(
					'Invalid JSON string provided.',
					'gravity-notification-filter'
				)
			);
		}

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'invalid_json_structure',
				__(
					'JSON configuration must contain an object or array.',
					'gravity-notification-filter'
				)
			);
		}

		return self::sanitize_exclusions_array(
			$decoded
		);
	}
}