<?php
/**
 * OSA — register custom pattern categories.
 *
 * Patterns themselves live as PHP files under /patterns/ and are auto-loaded
 * by WordPress because each file declares a Pattern Name header. We only
 * need to register custom categories here.
 *
 * @package OSA_Arsenal
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_pattern_category( 'osa-section', [
		'label'       => __( 'OSA · Sections', 'osa-arsenal' ),
		'description' => __( 'Brutalist-catalog page sections from the OSA design.', 'osa-arsenal' ),
	] );
	register_block_pattern_category( 'osa-chrome', [
		'label'       => __( 'OSA · Chrome', 'osa-arsenal' ),
		'description' => __( 'Headers, footers, marquees and overlays.', 'osa-arsenal' ),
	] );
}, 9 );
