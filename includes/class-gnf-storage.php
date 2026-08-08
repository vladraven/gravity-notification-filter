<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Storage {

	private const OPTION_NAME = 'gnf_excluded_fields';

	public static function make_context_key( int $form_id, string $notification_id = '' ): string {
		if ( $form_id <= 0 ) {
			return '';
		}

		if ( '' === $notification_id ) {
			return (string) $form_id;
		}

		$notification_id = GNF_Validator::sanitize_notification_id(
			$notification_id
		);

		if ( '' === $notification_id ) {
			return '';
		}

		return sprintf(
			'%d_n_%s',
			$form_id,
			$notification_id
		);
	}

	public static function get_all_exclusions(): array {
		$data = get_option(
			self::OPTION_NAME,
			[]
		);

		return GNF_Validator::sanitize_exclusions_array(
			$data
		);
	}

	public static function get_excluded_fields_for_context(
		string $context_key
	): array {
		$clean_key = GNF_Validator::sanitize_context_key(
			$context_key
		);

		if ( '' === $clean_key ) {
			return [];
		}

		$all = self::get_all_exclusions();

		return $all[ $clean_key ] ?? [];
	}

	public static function get_effective_excluded_fields(
		int $form_id,
		string $notification_id = ''
	): array {
		if ( $form_id <= 0 ) {
			return [];
		}

		$global_key = self::make_context_key(
			$form_id
		);

		if ( '' === $global_key ) {
			return [];
		}

		$effective = self::get_excluded_fields_for_context(
			$global_key
		);

		if ( '' !== $notification_id ) {
			$notification_key = self::make_context_key(
				$form_id,
				$notification_id
			);

			if ( '' !== $notification_key ) {
				$notification_excluded =
					self::get_excluded_fields_for_context(
						$notification_key
					);

				$effective = array_merge(
					$effective,
					$notification_excluded
				);
			}
		}

		return array_values(
			array_unique(
				$effective
			)
		);
	}

	public static function save_context_exclusions(
		string $context_key,
		array $field_ids
	): bool {
		$clean_key = GNF_Validator::sanitize_context_key(
			$context_key
		);

		if ( '' === $clean_key ) {
			return false;
		}

		$all = self::get_all_exclusions();

		$clean_field_ids = [];

		foreach ( $field_ids as $id ) {
			$sanitized = GNF_Validator::sanitize_field_id(
				$id
			);

			if ( '' !== $sanitized ) {
				$clean_field_ids[] = $sanitized;
			}
		}

		$clean_field_ids = array_values(
			array_unique(
				$clean_field_ids
			)
		);

		if ( empty( $clean_field_ids ) ) {
			unset( $all[ $clean_key ] );
		} else {
			$all[ $clean_key ] = $clean_field_ids;
		}

		$updated = update_option(
			self::OPTION_NAME,
			$all
		);

		if ( $updated ) {
			return true;
		}

		$current = get_option(
			self::OPTION_NAME,
			[]
		);

		$current = GNF_Validator::sanitize_exclusions_array(
			$current
		);

		return $current === $all;
	}

	public static function update_all_exclusions(
		array $data
	): bool {
		$clean = GNF_Validator::sanitize_exclusions_array(
			$data
		);

		$updated = update_option(
			self::OPTION_NAME,
			$clean
		);

		if ( $updated ) {
			return true;
		}

		$current = get_option(
			self::OPTION_NAME,
			[]
		);

		$current = GNF_Validator::sanitize_exclusions_array(
			$current
		);

		return $current === $clean;
	}

	public static function is_field_excluded(
		int $form_id,
		string|int $field_id,
		string $notification_id = ''
	): bool {
		$target_id = (string) $field_id;

		$effective = self::get_effective_excluded_fields(
			$form_id,
			$notification_id
		);

		return in_array(
			$target_id,
			$effective,
			true
		);
	}
}