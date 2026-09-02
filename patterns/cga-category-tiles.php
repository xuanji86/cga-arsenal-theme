<?php
/**
 * Title: CGA · Category Tiles
 * Slug: cga-arsenal/category-tiles
 * Categories: osa-section
 * Inserter: yes
 *
 * "Shop the counter" — the five top-level categories with live product
 * counts and their category images. Heading/aside are Customizer-driven
 * (CGA · Homepage → Categories & shelf); the tiles themselves are data.
 *
 * @package OSA_Arsenal
 */

$cga_cat_slugs = [ 'handguns', 'long-guns', 'receivers', 'ammunition', 'optics' ];
?>
<!-- wp:html -->
<div class="cga-page">
	<section class="home-block">
		<div class="cga-section">
			<h2 class="cga-section__title"><?php echo esc_html( cga_text( 'cga_cats_heading', CGA_CATS_HEADING_DEFAULT ) ); ?></h2>
			<p class="cga-section__aside"><?php echo esc_html( cga_text( 'cga_cats_aside', CGA_CATS_ASIDE_DEFAULT ) ); ?></p>
		</div>
		<div class="cga-cats">
			<?php
			foreach ( $cga_cat_slugs as $cga_slug ) :
				$cga_term = get_term_by( 'slug', $cga_slug, 'product_cat' );
				if ( ! $cga_term || is_wp_error( $cga_term ) ) {
					continue;
				}
				$cga_thumb_id  = (int) get_term_meta( $cga_term->term_id, 'thumbnail_id', true );
				$cga_thumb_url = $cga_thumb_id ? wp_get_attachment_image_url( $cga_thumb_id, 'medium' ) : '';
				// Umbrella tiles: POS pushes products into child categories (Pistol under
				// Handguns…). get_term_by() suppresses the WC count filter, so read WC's
				// children-inclusive count meta; the raw term count only sees direct products.
				$cga_wc_count  = get_term_meta( $cga_term->term_id, 'product_count_product_cat', true );
				$cga_count     = '' === $cga_wc_count ? (int) $cga_term->count : (int) $cga_wc_count;
				?>
			<a class="cga-cat" href="<?php echo esc_url( get_term_link( $cga_term ) ); ?>">
				<div class="cga-cat__media<?php echo $cga_thumb_url ? '' : ' cga-ph'; ?>">
					<?php if ( $cga_thumb_url ) : ?>
					<img src="<?php echo esc_url( $cga_thumb_url ); ?>" alt="<?php echo esc_attr( $cga_term->name ); ?>" loading="lazy" />
					<?php else : ?>
					<?php echo esc_html( strtolower( $cga_term->name ) ); ?>
					<?php endif; ?>
				</div>
				<div class="cga-cat__label"><span><?php echo esc_html( $cga_term->name ); ?></span><span><?php echo esc_html( (string) $cga_count ); ?></span></div>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
</div>
<!-- /wp:html -->
