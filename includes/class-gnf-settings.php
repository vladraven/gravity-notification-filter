<?php

defined( 'ABSPATH' ) || exit;

final class GNF_Settings {

	public static function register_settings(): void {
		register_setting(
			'gnf_settings_group',
			'gnf_excluded_fields',
			[
				'type'              => 'array',
				'sanitize_callback' => [ 'GNF_Validator', 'sanitize_exclusions_array' ],
				'default'           => [],
			]
		);
	}
}