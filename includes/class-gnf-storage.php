<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Storage {

	private const OPTION_NAME = 'gnf_excluded_fields';

	public static function get_all_exclusions(): array {
		$data = get_option( self::OPTION_NAME, [] );
		return GNF_Validator::sanitize_exclusions_array( $data );
	}

	public static function get_excluded_fields_for_context( string $context_key ): array {
		$all = self::get_all_exclusions();
		return $all[ $context_key ] ?? [];
	}

	public static function save_context_exclusions( string $context_key, array $field_ids ): bool {
		$all = self::get_all_exclusions();

		$clean_field_ids = [];
		foreach ( $field_ids as $id ) {
			$sanitized = GNF_Validator::sanitize_field_id( $id );
			if ( '' !== $sanitized ) {
				$clean_field_ids[] = $sanitized;
			}
		}

		$clean_field_ids = array_values( array_unique( $clean_field_ids ) );

		if ( empty( $clean_field_ids ) ) {
			unset( $all[ $context_key ] );
		} else {
			$all[ $context_key ] = $clean_field_ids;
		}

		return update_option( self::OPTION_NAME, $all );
	}

	public static function update_all_exclusions( array $data ): bool {
		$clean = GNF_Validator::sanitize_exclusions_array( $data );
		return update_option( self::OPTION_NAME, $clean );
	}

	public static function is_field_excluded( int $form_id, string|int $field_id, string $notification_id = '' ): bool {
		$all       = self::get_all_exclusions();
		$target_id = (string) $field_id;

		// 1. Check Notification-specific exclusion rule
		if ( ! empty( $notification_id ) ) {
			$notification_key = sprintf( '%d_n_%s', $form_id, $notification_id );
			if ( isset( $all[ $notification_key ] ) && in_array( $target_id, $all[ $notification_key ], true ) ) {
				return true;
			}
		}

		// 2. Check Global Form exclusion rule
		$global_key = (string) $form_id;
		if ( isset( $all[ $global_key ] ) && in_array( $target_id, $all[ $global_key ], true ) ) {
			return true;
		}

		return false;
	}
}