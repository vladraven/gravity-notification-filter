<?php
defined( 'ABSPATH' ) || exit;
/**
 * Admin Settings View v1.1.3
 *
 * @var array $forms Array of forms ['id' => int, 'title' => string]
 */
?>
<div class="wrap gnf-wrap">
	<h1 class="gnf-title">
		<span class="dashicons dashicons-filter"></span>
		<?php esc_html_e( 'Gravity Forms Notification Manager', 'gravity-notification-filter' ); ?>
		<span class="gnf-badge">v1.1.3</span>
	</h1>

	<div class="gnf-container">
		<!-- Sidebar: Form Selection -->
		<aside class="gnf-sidebar">
			<h2><?php esc_html_e( 'Forms', 'gravity-notification-filter' ); ?></h2>
			<?php if ( empty( $forms ) ) : ?>
				<p class="gnf-empty"><?php esc_html_e( 'No Gravity Forms found.', 'gravity-notification-filter' ); ?></p>
			<?php else : ?>
				<ul class="gnf-form-list" id="gnf-form-list">
					<?php foreach ( $forms as $index => $form ) : ?>
						<li>
							<button type="button" 
									class="gnf-form-btn <?php echo 0 === $index ? 'active' : ''; ?>" 
									data-form-id="<?php echo esc_attr( $form['id'] ); ?>">
								<span class="dashicons dashicons-feedback"></span>
								<span class="gnf-form-name"><?php echo esc_html( $form['title'] ); ?></span>
								<span class="gnf-form-id">ID: <?php echo esc_html( $form['id'] ); ?></span>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</aside>

		<!-- Main Workspace -->
		<main class="gnf-main">
			<div id="gnf-loading" class="gnf-loading" style="display:none;">
				<span class="spinner is-active"></span> <?php esc_html_e( 'Loading workspace...', 'gravity-notification-filter' ); ?>
			</div>

			<div id="gnf-workspace" class="gnf-workspace">
				<!-- Context Switcher (Global vs Notification) -->
				<div class="gnf-context-bar">
					<label for="gnf-notification-select"><strong><?php esc_html_e( 'Notification Context:', 'gravity-notification-filter' ); ?></strong></label>
					<select id="gnf-notification-select">
						<option value="global"><?php esc_html_e( 'Global (All Notifications for this form)', 'gravity-notification-filter' ); ?></option>
					</select>
				</div>

				<!-- Header Controls -->
				<header class="gnf-workspace-header">
					<div class="gnf-search-box">
						<input type="text" id="gnf-field-search" placeholder="<?php esc_attr_e( 'Search field...', 'gravity-notification-filter' ); ?>" />
					</div>
					<div class="gnf-filter-tabs">
						<button type="button" class="gnf-tab active" data-filter="all"><?php esc_html_e( 'All', 'gravity-notification-filter' ); ?></button>
						<button type="button" class="gnf-tab" data-filter="hidden"><?php esc_html_e( 'Only Hidden', 'gravity-notification-filter' ); ?></button>
						<button type="button" class="gnf-tab" data-filter="visible"><?php esc_html_e( 'Only Visible', 'gravity-notification-filter' ); ?></button>
						<button type="button" class="gnf-tab" data-filter="admin"><?php esc_html_e( 'Administrative', 'gravity-notification-filter' ); ?></button>
					</div>
				</header>

				<!-- Quick Presets Tool -->
				<div class="gnf-presets-bar">
					<span><strong><?php esc_html_e( 'Presets:', 'gravity-notification-filter' ); ?></strong></span>
					<button type="button" id="gnf-preset-hide-admin" class="button button-small"><?php esc_html_e( 'Hide All Admin Fields', 'gravity-notification-filter' ); ?></button>
					<button type="button" id="gnf-preset-show-all" class="button button-small"><?php esc_html_e( 'Show All Fields (Reset)', 'gravity-notification-filter' ); ?></button>
				</div>

				<!-- Field List Table -->
				<section class="gnf-card">
					<h2><?php esc_html_e( 'Fields Configuration', 'gravity-notification-filter' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Check a box to HIDE the field or sub-field from {all_fields} merge tag in notification emails.', 'gravity-notification-filter' ); ?>
					</p>

					<div class="gnf-table-wrapper">
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th class="column-cb" style="width: 50px;"><?php esc_html_e( 'Hide', 'gravity-notification-filter' ); ?></th>
									<th><?php esc_html_e( 'Label', 'gravity-notification-filter' ); ?></th>
									<th><?php esc_html_e( 'Admin Label', 'gravity-notification-filter' ); ?></th>
									<th><?php esc_html_e( 'Type', 'gravity-notification-filter' ); ?></th>
									<th style="width: 80px;"><?php esc_html_e( 'Field ID', 'gravity-notification-filter' ); ?></th>
								</tr>
							</thead>
							<tbody id="gnf-fields-body">
								<!-- Populated via JS -->
							</tbody>
						</table>
					</div>

					<div class="gnf-actions">
						<button type="button" id="gnf-save-btn" class="button button-primary button-hero">
							<?php esc_html_e( 'Save Changes', 'gravity-notification-filter' ); ?>
						</button>
						<span id="gnf-save-notice" class="gnf-notice"></span>
					</div>
				</section>

				<!-- Notification Preview -->
				<section class="gnf-card gnf-preview-card">
					<h2><?php esc_html_e( 'Notification Preview', 'gravity-notification-filter' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Real-time simulation of fields included in {all_fields}.', 'gravity-notification-filter' ); ?></p>
					<div id="gnf-preview-list" class="gnf-preview-list">
						<!-- Populated via JS -->
					</div>
				</section>
			</div>
		</main>
	</div>

	<!-- Export / Import Section -->
	<div class="gnf-card gnf-tools-card">
		<h2><?php esc_html_e( 'Export / Import Configuration', 'gravity-notification-filter' ); ?></h2>
		<div class="gnf-tools-grid">
			<div class="gnf-tool-box">
				<h3><?php esc_html_e( 'Export', 'gravity-notification-filter' ); ?></h3>
				<p><?php esc_html_e( 'Download or copy current excluded fields configuration JSON.', 'gravity-notification-filter' ); ?></p>
				<button type="button" id="gnf-export-btn" class="button"><?php esc_html_e( 'Export JSON', 'gravity-notification-filter' ); ?></button>
			</div>
			<div class="gnf-tool-box">
				<h3><?php esc_html_e( 'Import', 'gravity-notification-filter' ); ?></h3>
				<p><?php esc_html_e( 'Paste JSON configuration to overwrite settings.', 'gravity-notification-filter' ); ?></p>
				<textarea id="gnf-import-textarea" rows="3" placeholder="<?php esc_attr_e( 'Paste JSON here...', 'gravity-notification-filter' ); ?>"></textarea>
				<button type="button" id="gnf-import-btn" class="button button-secondary" style="margin-top: 8px;">
					<?php esc_html_e( 'Import JSON', 'gravity-notification-filter' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>