<?php

defined( 'ABSPATH' ) || exit;

final class GNF_CLI {

	public static function register_commands(): void {
		WP_CLI::add_command( 'gnf', self::class );
	}

	/**
	 * Lists excluded field IDs for a form or notification context.
	 *
	 * ## OPTIONS
	 *
	 * --form_id=<form_id>
	 * : The ID of the Gravity Form.
	 *
	 * [--notification_id=<notification_id>]
	 * : Optional notification ID context.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gnf list --form_id=2
	 *     wp gnf list --form_id=2 --notification_id=5a3b
	 */
	public function list( array $args, array $assoc_args ): void {
		$form_id = absint( $assoc_args['form_id'] ?? 0 );

		$notification_id = GNF_Validator::sanitize_notification_id(
			$assoc_args['notification_id'] ?? ''
		);

		if ( $form_id <= 0 ) {
			WP_CLI::error( 'Valid --form_id is required.' );
		}

		$context_key = GNF_Storage::make_context_key(
			$form_id,
			$notification_id
		);

		if ( '' === $context_key ) {
			WP_CLI::error( 'Invalid context.' );
		}

		$excluded = GNF_Storage::get_excluded_fields_for_context(
			$context_key
		);

		if ( empty( $excluded ) ) {
			WP_CLI::success(
				sprintf(
					'No excluded fields found for context [%s].',
					$context_key
				)
			);

			return;
		}

		WP_CLI::log(
			sprintf(
				'Excluded Field IDs for [%s]: %s',
				$context_key,
				implode( ', ', $excluded )
			)
		);
	}

	/**
	 * Sets excluded field IDs for a form or notification context.
	 *
	 * ## OPTIONS
	 *
	 * --form_id=<form_id>
	 * : The ID of the Gravity Form.
	 *
	 * [--field_ids=<field_ids>]
	 * : Comma-separated list of field IDs to exclude.
	 *
	 * [--notification_id=<notification_id>]
	 * : Optional notification ID context.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gnf exclude --form_id=2 --field_ids=14,17,25
	 *     wp gnf exclude --form_id=2 --field_ids=""
	 */
	public function exclude( array $args, array $assoc_args ): void {
		$form_id = absint( $assoc_args['form_id'] ?? 0 );

		$raw_fields = isset( $assoc_args['field_ids'] )
			? (string) $assoc_args['field_ids']
			: '';

		$notification_id = GNF_Validator::sanitize_notification_id(
			$assoc_args['notification_id'] ?? ''
		);

		if ( $form_id <= 0 ) {
			WP_CLI::error( 'Valid --form_id is required.' );
		}

		if (
			isset( $assoc_args['notification_id'] )
			&& '' === $notification_id
		) {
			WP_CLI::error( 'Invalid --notification_id.' );
		}

		$field_ids = [];

		if ( '' !== trim( $raw_fields ) ) {
			foreach ( explode( ',', $raw_fields ) as $field_id ) {
				$sanitized = GNF_Validator::sanitize_field_id(
					trim( $field_id )
				);

				if ( '' !== $sanitized ) {
					$field_ids[] = $sanitized;
				}
			}
		}

		$context_key = GNF_Storage::make_context_key(
			$form_id,
			$notification_id
		);

		if ( '' === $context_key ) {
			WP_CLI::error( 'Invalid context.' );
		}

		$saved = GNF_Storage::save_context_exclusions(
			$context_key,
			$field_ids
		);

		if ( ! $saved ) {
			WP_CLI::error(
				sprintf(
					'Failed to update exclusions for context [%s].',
					$context_key
				)
			);
		}

		if ( empty( $field_ids ) ) {
			WP_CLI::success(
				sprintf(
					'Successfully cleared all exclusions for context [%s].',
					$context_key
				)
			);

			return;
		}

		WP_CLI::success(
			sprintf(
				'Successfully updated exclusions for context [%s].',
				$context_key
			)
		);
	}

	/**
	 * Exports all plugin configuration as JSON.
	 */
	public function export( array $args, array $assoc_args ): void {
		$exclusions = GNF_Storage::get_all_exclusions();

		$json = wp_json_encode(
			$exclusions,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);

		if ( false === $json ) {
			WP_CLI::error( 'Unable to encode configuration as JSON.' );
		}

		WP_CLI::line( $json );
	}

	/**
	 * Imports configuration from a JSON string or file.
	 *
	 * ## OPTIONS
	 *
	 * [--json=<json_string>]
	 * : Raw JSON configuration string.
	 *
	 * [--file=<file_path>]
	 * : Path to JSON configuration file.
	 *
	 * ## EXAMPLES
	 *
	 *     wp gnf import --file=config.json
	 *     wp gnf import --json='{"2":["14","17"]}'
	 */
	public function import( array $args, array $assoc_args ): void {
		$json_data = isset( $assoc_args['json'] )
			? (string) $assoc_args['json']
			: '';

		$file_path = isset( $assoc_args['file'] )
			? (string) $assoc_args['file']
			: '';

		if ( '' !== $json_data && '' !== $file_path ) {
			WP_CLI::error(
				'Use either --json or --file, not both.'
			);
		}

		if ( '' === $json_data && '' !== $file_path ) {
			if ( ! is_file( $file_path ) ) {
				WP_CLI::error(
					sprintf(
						'File not found: %s',
						$file_path
					)
				);
			}

			if ( ! is_readable( $file_path ) ) {
				WP_CLI::error(
					sprintf(
						'File is not readable: %s',
						$file_path
					)
				);
			}

			$contents = file_get_contents( $file_path );

			if ( false === $contents ) {
				WP_CLI::error(
					sprintf(
						'Unable to read file: %s',
						$file_path
					)
				);
			}

			$json_data = $contents;
		}

		if ( '' === trim( $json_data ) ) {
			WP_CLI::error(
				'Provide either --json or --file payload.'
			);
		}

		$validated = GNF_Validator::validate_json_import(
			$json_data
		);

		if ( is_wp_error( $validated ) ) {
			WP_CLI::error(
				$validated->get_error_message()
			);
		}

		if ( ! GNF_Storage::update_all_exclusions( $validated ) ) {
			WP_CLI::error(
				'Unable to save imported configuration.'
			);
		}

		WP_CLI::success(
			'Configuration imported successfully.'
		);
	}
}