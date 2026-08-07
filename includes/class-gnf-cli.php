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
		$form_id         = absint( $assoc_args['form_id'] ?? 0 );
		$notification_id = sanitize_text_field( $assoc_args['notification_id'] ?? '' );

		if ( $form_id <= 0 ) {
			WP_CLI::error( 'Valid --form_id is required.' );
		}

		$context_key = ! empty( $notification_id ) ? sprintf( '%d_n_%s', $form_id, trim( $notification_id ) ) : (string) $form_id;
		$excluded    = GNF_Storage::get_excluded_fields_for_context( $context_key );

		if ( empty( $excluded ) ) {
			WP_CLI::success( sprintf( 'No excluded fields found for context [%s].', $context_key ) );
			return;
		}

		WP_CLI::log( sprintf( 'Excluded Field IDs for [%s]: %s', $context_key, implode( ', ', $excluded ) ) );
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
	 * : Comma-separated list of field IDs to exclude (leave empty to clear).
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
		$form_id         = absint( $assoc_args['form_id'] ?? 0 );
		$raw_fields      = sanitize_text_field( $assoc_args['field_ids'] ?? '' );
		$notification_id = sanitize_text_field( $assoc_args['notification_id'] ?? '' );

		if ( $form_id <= 0 ) {
			WP_CLI::error( 'Valid --form_id is required.' );
		}

		$field_ids = [];
		if ( '' !== trim( $raw_fields ) ) {
			$raw_array = explode( ',', $raw_fields );
			foreach ( $raw_array as $fid ) {
				$sanitized = GNF_Validator::sanitize_field_id( $fid );
				if ( '' !== $sanitized ) {
					$field_ids[] = $sanitized;
				}
			}
		}

		$context_key = ! empty( $notification_id ) ? sprintf( '%d_n_%s', $form_id, trim( $notification_id ) ) : (string) $form_id;

		GNF_Storage::save_context_exclusions( $context_key, $field_ids );

		if ( empty( $field_ids ) ) {
			WP_CLI::success( sprintf( 'Successfully cleared all exclusions for context [%s].', $context_key ) );
		} else {
			WP_CLI::success( sprintf( 'Successfully updated exclusions for context [%s].', $context_key ) );
		}
	}

	/**
	 * Exports all plugin configuration as JSON.
	 */
	public function export( array $args, array $assoc_args ): void {
		$exclusions = GNF_Storage::get_all_exclusions();
		WP_CLI::line( json_encode( $exclusions, JSON_PRETTY_PRINT ) );
	}
}