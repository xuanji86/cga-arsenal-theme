<?php
/**
 * OSA — "Shop by Model" repeater Customizer control.
 *
 * Replaces the raw `osa_model_links` textarea with a friendly repeater + WP
 * Media Library image picker, so the shop owner maintains the model-landing
 * list without hand-editing the `Label|URL|ImageURL` pipe format.
 *
 * This control is a thin shell: render_content() prints the rows container and
 * the "Add model" button, and assets/js/customize-model-links.js builds the
 * rows from — and serializes them back to — the SAME `osa_model_links` setting.
 * The storage format (newline `Label|URL|ImageURL`) is unchanged and the
 * setting's server-side `sanitize_textarea_field` stays the source of truth, so
 * blocks/model-links/render.php + osa_model_links_items() keep working untouched.
 *
 * Loaded from inc/customizer.php inside the `customize_register` hook, where the
 * WP_Customize_Control base class is guaranteed to be present.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'OSA_Model_Links_Control' ) ) {

	/**
	 * Media-picker repeater bound to the `osa_model_links` theme mod.
	 */
	class OSA_Model_Links_Control extends WP_Customize_Control {

		/**
		 * Control type. assets/js/customize-model-links.js matches on this to
		 * find and hydrate every instance.
		 *
		 * @var string
		 */
		public $type = 'osa-model-links';

		/**
		 * Render the repeater shell.
		 *
		 * Rows are built client-side from the bound setting value (single source
		 * of truth), so there is no server-rendered row markup to drift out of
		 * sync with the JS serializer. Public (rather than the base class's
		 * protected) so it stays unit-testable without a full WP bootstrap.
		 */
		public function render_content() {
			?>
			<?php if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>
			<div class="osa-mlc" data-osa-mlc>
				<ul class="osa-mlc__rows" role="list"></ul>
				<button type="button" class="button osa-mlc__add">
					<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
					<?php esc_html_e( 'Add model', 'osa-arsenal' ); ?>
				</button>
				<p class="osa-mlc__hint description">
					<?php esc_html_e( 'Label and URL are required. The image is optional and only used by the homepage cards. Filter slugs are a comma-separated list of pa_model term slugs (copied from the model-landing grid line, never guessed); they let the /firearms/ hub cards jump straight to the matching shop filter AND let every product of that model link back up to its landing page, so a row left without them leaves that model\'s product pages linking nowhere. Clear every row to hide the block across the site.', 'osa-arsenal' ); ?>
				</p>
			</div>
			<?php
		}
	}
}
