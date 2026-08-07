<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Storage {

	private const OPTION_NAME = 'gnf_excluded_fields';

	public static function get_all_exclusions(): array {
		$data = get_option( self::OPTION_NAME, [] );
		return GNF_Validator::sanitize_exclusions_array( $data );
	}

	public static function get_excluded_fields_for_form( int $form_id ): array {
		$all = self::get_all_exclusions();
		return $all[ $form_id ] ?? [];
	}

	public static function save_form_exclusions( int $form_id, array $field_ids ): bool {
		$all = self::get_all_exclusions();
		
		$clean_field_ids = array_values( array_unique( array_map( 'absint', $field_ids ) ) );
		$clean_field_ids = array_filter( $clean_field_ids, static fn( $id ) => $id > 0 );

		if ( empty( $clean_field_ids ) ) {
			unset( $all[ $form_id ] );
		} else {
			$all[ $form_id ] = $clean_field_ids;
		}

		return update_option( self::OPTION_NAME, $all );
	}

	public static function update_all_exclusions( array $data ): bool {
		$clean = GNF_Validator::sanitize_exclusions_array( $data );
		return update_option( self::OPTION_NAME, $clean );
	}

	public static function is_field_excluded( int $form_id, int $field_id ): bool {
		$excluded = self::get_excluded_fields_for_form( $form_id );
		return in_array( $field_id, $excluded, true );
	}
}