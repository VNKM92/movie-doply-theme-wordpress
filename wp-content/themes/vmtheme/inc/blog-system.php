<?php
/**
 * VMTheme Advanced Blog & Editorial System
 *
 * Provides reading time calculators, fallback dummy poster resolvers,
 * post views counters, interactive social share bars, author bio modules,
 * related posts queries, and sample editorial seeder.
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Fallback Blog Placeholder Banner URL
 *
 * @return string
 */
function vmtheme_get_fallback_blog_poster_url() {
	$url = get_template_directory_uri() . '/assets/images/blog-placeholder.svg';
	return apply_filters( 'vmtheme_fallback_blog_poster_url', $url );
}

if ( ! function_exists( 'doodhtheme_get_fallback_blog_poster_url' ) ) {
	function doodhtheme_get_fallback_blog_poster_url() {
		return vmtheme_get_fallback_blog_poster_url();
	}
}

/**
 * Get Blog Featured Image or Fallback Dummy Banner
 *
 * @param int|null $post_id
 * @param string   $size
 * @return string
 */
function vmtheme_get_blog_thumbnail_url( $post_id = null, $size = 'large' ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) {
		return vmtheme_get_fallback_blog_poster_url();
	}

	if ( has_post_thumbnail( $post_id ) ) {
		$thumb = get_the_post_thumbnail_url( $post_id, $size );
		if ( ! empty( $thumb ) ) {
			return $thumb;
		}
	}

	// Check custom backdrop or poster meta
	$custom_img = get_post_meta( $post_id, '_doodh_backdrop_url', true ) ?: get_post_meta( $post_id, '_doodh_poster_url', true );
	if ( ! empty( $custom_img ) ) {
		return $custom_img;
	}

	return vmtheme_get_fallback_blog_poster_url();
}

if ( ! function_exists( 'doodhtheme_get_blog_thumbnail_url' ) ) {
	function doodhtheme_get_blog_thumbnail_url( $post_id = null, $size = 'large' ) {
		return vmtheme_get_blog_thumbnail_url( $post_id, $size );
	}
}

/**
 * Calculate Estimated Reading Time in Minutes
 *
 * @param int|null $post_id
 * @return string
 */
function vmtheme_get_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = ceil( $words / 200 );

	if ( $minutes <= 1 ) {
		return __( '1 min read', 'vmtheme' );
	}
	return sprintf( __( '%d min read', 'vmtheme' ), $minutes );
}

if ( ! function_exists( 'doodhtheme_get_reading_time' ) ) {
	function doodhtheme_get_reading_time( $post_id = null ) {
		return vmtheme_get_reading_time( $post_id );
	}
}

/**
 * Track & Increment Post View Counts
 *
 * @param int $post_id
 */
function vmtheme_set_post_views( $post_id ) {
	if ( ! is_single() || empty( $post_id ) ) {
		return;
	}

	$count_key = '_vmtheme_post_views';
	$count     = (int) get_post_meta( $post_id, $count_key, true );

	if ( $count === 0 ) {
		// Check fallback key
		$count = (int) get_post_meta( $post_id, '_doodh_views', true );
		if ( $count === 0 ) {
			$count = mt_rand( 120, 850 ); // Initial realistic view count
		}
	} else {
		$count++;
	}

	update_post_meta( $post_id, $count_key, $count );
	update_post_meta( $post_id, '_doodh_views', $count );
}

/**
 * Get Formatted Post Views
 *
 * @param int|null $post_id
 * @return string
 */
function vmtheme_get_post_views( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$views   = (int) get_post_meta( $post_id, '_vmtheme_post_views', true );
	if ( ! $views ) {
		$views = (int) get_post_meta( $post_id, '_doodh_views', true );
	}
	if ( ! $views ) {
		$views = 150;
	}

	if ( $views >= 1000000 ) {
		return round( $views / 1000000, 1 ) . 'M';
	}
	if ( $views >= 1000 ) {
		return round( $views / 1000, 1 ) . 'K';
	}
	return (string) $views;
}

if ( ! function_exists( 'doodhtheme_get_post_views' ) ) {
	function doodhtheme_get_post_views( $post_id = null ) {
		return vmtheme_get_post_views( $post_id );
	}
}

/**
 * Render Interactive Social Share Bar
 *
 * @param int|null $post_id
 */
function vmtheme_render_social_share_bar( $post_id = null ) {
	$post_id   = $post_id ?: get_the_ID();
	$url       = urlencode( get_permalink( $post_id ) );
	$title     = urlencode( get_the_title( $post_id ) );
	$raw_url   = get_permalink( $post_id );
	?>
	<div class="vmtheme-share-bar">
		<span class="vmtheme-share-label"><i class="fas fa-share-alt"></i> <?php esc_html_e( 'Share Article:', 'vmtheme' ); ?></span>
		<div class="vmtheme-share-buttons">
			<a href="https://api.whatsapp.com/send?text=<?php echo $title . '%20' . $url; ?>" target="_blank" rel="noopener noreferrer" class="vm-share-btn vm-share-whatsapp" title="Share on WhatsApp">
				<i class="fab fa-whatsapp"></i>
			</a>
			<a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener noreferrer" class="vm-share-btn vm-share-telegram" title="Share on Telegram">
				<i class="fab fa-telegram-plane"></i>
			</a>
			<a href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener noreferrer" class="vm-share-btn vm-share-twitter" title="Share on X (Twitter)">
				<i class="fab fa-x-twitter"></i>
			</a>
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="vm-share-btn vm-share-facebook" title="Share on Facebook">
				<i class="fab fa-facebook-f"></i>
			</a>
			<a href="https://reddit.com/submit?url=<?php echo $url; ?>&title=<?php echo $title; ?>" target="_blank" rel="noopener noreferrer" class="vm-share-btn vm-share-reddit" title="Share on Reddit">
				<i class="fab fa-reddit-alien"></i>
			</a>
			<button type="button" class="vm-share-btn vm-share-copy" onclick="vmthemeCopyArticleLink('<?php echo esc_js( $raw_url ); ?>', this)" title="Copy Link to Clipboard">
				<i class="fas fa-link"></i>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Render Author Bio Box
 *
 * @param int|null $author_id
 */
function vmtheme_render_author_bio( $author_id = null ) {
	$author_id   = $author_id ?: get_the_author_meta( 'ID' );
	$name        = get_the_author_meta( 'display_name', $author_id );
	$bio         = get_the_author_meta( 'description', $author_id );
	$avatar_url  = get_avatar_url( $author_id, array( 'size' => 120 ) );
	$author_link = get_author_posts_url( $author_id );
	$post_count  = count_user_posts( $author_id, 'post' );

	if ( empty( $bio ) ) {
		$bio = sprintf( __( 'Senior Cinema Journalist & Entertainment Critic covering industry trends, box-office analytics, and director retrospectives for %s.', 'vmtheme' ), get_bloginfo( 'name' ) );
	}
	?>
	<div class="vmtheme-author-card">
		<div class="vmtheme-author-avatar-wrap">
			<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="vmtheme-author-avatar">
		</div>
		<div class="vmtheme-author-info">
			<div class="vmtheme-author-badge"><?php esc_html_e( 'Article Author', 'vmtheme' ); ?></div>
			<h4 class="vmtheme-author-name">
				<a href="<?php echo esc_url( $author_link ); ?>"><?php echo esc_html( $name ); ?></a>
			</h4>
			<p class="vmtheme-author-bio"><?php echo esc_html( $bio ); ?></p>
			<div class="vmtheme-author-meta">
				<span><i class="fas fa-newspaper"></i> <?php echo sprintf( _n( '%d Published Article', '%d Published Articles', $post_count, 'vmtheme' ), $post_count ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render Related Blog Posts
 *
 * @param int|null $post_id
 * @param int      $limit
 */
function vmtheme_render_related_blog_posts( $post_id = null, $limit = 3 ) {
	$post_id    = $post_id ?: get_the_ID();
	$categories = wp_get_post_categories( $post_id );

	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => $limit,
		'post__not_in'   => array( $post_id ),
		'orderby'        => 'rand',
	);

	if ( ! empty( $categories ) ) {
		$args['category__in'] = $categories;
	}

	$related_query = new WP_Query( $args );

	if ( ! $related_query->have_posts() ) {
		// Fallback to recent posts if no category match
		unset( $args['category__in'] );
		$args['orderby'] = 'date';
		$related_query   = new WP_Query( $args );
	}

	if ( ! $related_query->have_posts() ) {
		return;
	}
	?>
	<div class="vmtheme-related-posts-section">
		<div class="doodh-section-header">
			<h3 class="doodh-section-title">
				<i class="fas fa-film" style="color:var(--dt-primary);"></i> <?php esc_html_e( 'Related Cinema News & Articles', 'vmtheme' ); ?>
			</h3>
		</div>
		<div class="vmtheme-blog-grid vmtheme-blog-related-grid">
			<?php
			while ( $related_query->have_posts() ) :
				$related_query->the_post();
				$rel_id       = get_the_ID();
				$rel_thumb    = vmtheme_get_blog_thumbnail_url( $rel_id, 'medium_large' );
				$rel_cats     = get_the_category( $rel_id );
				$rel_cat_name = ! empty( $rel_cats ) ? $rel_cats[0]->name : __( 'Cinema News', 'vmtheme' );
				$rel_read     = vmtheme_get_reading_time( $rel_id );
				?>
				<article class="vmtheme-blog-card">
					<div class="vmtheme-blog-card-thumb-wrap">
						<img src="<?php echo esc_url( $rel_thumb ); ?>" alt="<?php the_title_attribute(); ?>" class="vmtheme-blog-card-thumb" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( vmtheme_get_fallback_blog_poster_url() ); ?>';">
						<span class="vmtheme-blog-cat-badge"><?php echo esc_html( $rel_cat_name ); ?></span>
						<a href="<?php the_permalink(); ?>" class="vmtheme-blog-card-overlay"></a>
					</div>
					<div class="vmtheme-blog-card-body">
						<div class="vmtheme-blog-meta-top">
							<span><i class="far fa-calendar-alt"></i> <?php echo get_the_date( 'M j, Y' ); ?></span>
							<span><i class="far fa-clock"></i> <?php echo esc_html( $rel_read ); ?></span>
						</div>
						<h4 class="vmtheme-blog-card-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h4>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
}

/**
 * Automatically Seed High-Quality Sample Blog Posts if None Exist
 */
function vmtheme_seed_sample_blog_posts_if_empty() {
	if ( get_option( 'vmtheme_blog_posts_seeded' ) ) {
		return;
	}

	$existing_count = wp_count_posts( 'post' )->publish ?? 0;
	if ( $existing_count >= 3 ) {
		update_option( 'vmtheme_blog_posts_seeded', 1 );
		return;
	}

	// Create Categories safely
	$get_or_create_cat = function( $name ) {
		$term = get_term_by( 'name', $name, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}
		$created = wp_insert_term( $name, 'category' );
		if ( ! is_wp_error( $created ) && isset( $created['term_id'] ) ) {
			return (int) $created['term_id'];
		}
		return 1;
	};

	$cat_industry = $get_or_create_cat( 'Industry News' );
	$cat_reviews  = $get_or_create_cat( 'Film Analysis' );
	$cat_guides   = $get_or_create_cat( 'Streaming Guides' );

	$admin_user = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
	$author_id  = ! empty( $admin_user ) ? $admin_user[0]->ID : 1;

	$sample_articles = array(
		array(
			'title'    => 'The Evolution of 4K HDR Cinema: Why High Bitrate Streaming Matters in 2026',
			'category' => array( $cat_industry, $cat_guides ),
			'content'  => "<!-- wp:paragraph --><p class=\"vmtheme-lead\">Cinema technology has leaped forward with groundbreaking advances in High Dynamic Range (HDR10+, Dolby Vision) and lossless audio formatting. Understanding how streaming architectures deliver pristine master-quality video directly to OLED displays transforms your home theater experience.</p><!-- /wp:paragraph --><h2>The Technical Difference: Bitrate vs Resolution</h2><p>Resolution alone describes the pixel grid, but bitrate determines the compression fidelity. A 4K video compressed at low bandwidth suffers from macro-blocking during fast action sequences, whereas high-bitrate streaming preserves true director intent, specular highlights, and shadow gradients.</p><blockquote>\"True cinema fidelity is not merely about 3840x2160 pixels—it is about color gamut depth, spatial audio staging, and dynamic luminance headroom.\"</blockquote><h2>Optimizing Your Home Theater Setup</h2><p>To experience films as their creators intended, ensure your playback client supports hardware HEVC / AV1 decoding, Gigabit network buffering, and calibrated gamma curves. Explore our 4K Ultra HD collection to test your setup with reference master streams.</p>",
		),
		array(
			'title'    => 'Mastering the Narrative Arc: How Modern Sci-Fi Directors Redefine World-Building',
			'category' => array( $cat_reviews ),
			'content'  => "<!-- wp:paragraph --><p class=\"vmtheme-lead\">From Christopher Nolan's temporal paradoxes to Denis Villeneuve's grand brutalist scale in modern space epics, contemporary science fiction has reached a new golden era of thoughtful cinematic storytelling.</p><!-- /wp:paragraph --><h2>Practical Effects Harmonized with Neural CGI</h2><p>Modern masterpieces blend massive physical set constructions with seamless photorealistic visual effects. This tactile realism anchors the audience emotionally, providing tangible weight to speculative technology and distant galactic politics.</p><h2>The Sound Design Revolution</h2><p>Sub-bass acoustic resonance, micro-tonal synthesizer scores by composers like Hans Zimmer and Ludwig Göransson, and immersive Dolby Atmos object tracking turn cinema soundscapes into visceral narrative instruments.</p>",
		),
		array(
			'title'    => 'Top 10 Must-Watch Mystery & Thriller Series to Binge This Weekend',
			'category' => array( $cat_guides, $cat_reviews ),
			'content'  => "<!-- wp:paragraph --><p class=\"vmtheme-lead\">Looking for intense plot twists, cerebral detective puzzles, and edge-of-your-seat suspense? We have curated the ultimate ranking of critically acclaimed mystery and thriller television series.</p><!-- /wp:paragraph --><h2>1. The Psychological Detective Masterpiece</h2><p>Atmospheric neo-noir storytelling, dual-timeline investigations, and complex character psychology make this an unforgettable binge experience.</p><h2>2. The High-Stakes Sci-Fi Conspiracy</h2><p>When corporate secrets intersect with deep-space discovery, every episode ends with an irresistible cliffhanger. Add all these titles to your personal Watchlist today!</p>",
		),
	);

	foreach ( $sample_articles as $art ) {
		$post_data = array(
			'post_title'    => $art['title'],
			'post_content'  => $art['content'],
			'post_status'   => 'publish',
			'post_author'   => $author_id,
			'post_type'     => 'post',
			'post_category' => is_array( $art['category'] ) ? $art['category'] : array( $art['category'] ),
		);
		$new_id = wp_insert_post( $post_data );
		if ( ! is_wp_error( $new_id ) && $new_id ) {
			update_post_meta( $new_id, '_vmtheme_post_views', mt_rand( 280, 1420 ) );
		}
	}

	update_option( 'vmtheme_blog_posts_seeded', 1 );
}
add_action( 'after_switch_theme', 'vmtheme_seed_sample_blog_posts_if_empty' );
add_action( 'init', 'vmtheme_seed_sample_blog_posts_if_empty', 20 );

/**
 * Ensure Blog Page with page-blog.php Template Exists
 */
function vmtheme_ensure_blog_page_exists() {
	$blog_page = get_page_by_path( 'blog' );
	if ( ! $blog_page ) {
		$page_id = wp_insert_post( array(
			'post_title'   => 'Cinema Blog & Editorial',
			'post_name'    => 'blog',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-blog.php' );
		}
	} else {
		$template = get_post_meta( $blog_page->ID, '_wp_page_template', true );
		if ( $template !== 'page-blog.php' ) {
			update_post_meta( $blog_page->ID, '_wp_page_template', 'page-blog.php' );
		}
	}
}
add_action( 'init', 'vmtheme_ensure_blog_page_exists', 15 );
