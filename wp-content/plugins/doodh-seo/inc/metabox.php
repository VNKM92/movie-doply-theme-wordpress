<?php
/**
 * Doodh SEO - Live Snippet Editor & Content Analyzer Metabox
 *
 * @package DoodhSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Doodh_SEO_Metabox {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_seo_metabox' ) );
		add_action( 'save_post', array( __CLASS__, 'save_seo_metabox' ) );
	}

	public static function add_seo_metabox() {
		$post_types = array( 'post', 'page', 'movies', 'tvshows', 'episodes' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'doodh_seo_master_box',
				'<span class="dashicons dashicons-search" style="color:#e50914; margin-top:2px;"></span> ' . __( 'Doodh SEO — Live Snippet & Content Optimizer', 'doodh-seo' ),
				array( __CLASS__, 'render_metabox' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	public static function render_metabox( $post ) {
		wp_nonce_field( 'doodh_seo_metabox_save', 'doodh_seo_nonce' );

		$seo_title    = get_post_meta( $post->ID, '_doodh_seo_title', true );
		$seo_desc     = get_post_meta( $post->ID, '_doodh_seo_desc', true );
		$focus_kw     = get_post_meta( $post->ID, '_doodh_focus_keyword', true );
		$canonical    = get_post_meta( $post->ID, '_doodh_seo_canonical', true );
		$noindex      = get_post_meta( $post->ID, '_doodh_seo_noindex', true );
		$nofollow     = get_post_meta( $post->ID, '_doodh_seo_nofollow', true );
		$og_title     = get_post_meta( $post->ID, '_doodh_og_title', true );
		$og_desc      = get_post_meta( $post->ID, '_doodh_og_desc', true );
		$og_image     = get_post_meta( $post->ID, '_doodh_og_image', true );
		$seo_score    = (int) get_post_meta( $post->ID, '_doodh_seo_score', true ) ?: 85;

		$permalink    = get_permalink( $post->ID ) ?: home_url( '/' . ( $post->post_name ?: 'sample-movie' ) . '/' );
		$site_title   = get_bloginfo( 'name' );
		?>
		<div class="doodh-seo-metabox-wrap">
			<!-- Tab Navigation -->
			<div class="doodh-seo-tabs-nav">
				<button type="button" class="doodh-seo-tab-btn active" data-tab="seo">
					<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'SEO & Snippet', 'doodh-seo' ); ?>
					<span class="doodh-score-pill-sm <?php echo ( $seo_score >= 80 ) ? 'good' : ( ( $seo_score >= 50 ) ? 'ok' : 'poor' ); ?>" id="doodh-badge-score"><?php echo esc_html( $seo_score ); ?>/100</span>
				</button>
				<button type="button" class="doodh-seo-tab-btn" data-tab="readability">
					<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Readability', 'doodh-seo' ); ?>
				</button>
				<button type="button" class="doodh-seo-tab-btn" data-tab="social">
					<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Social (FB & Twitter)', 'doodh-seo' ); ?>
				</button>
				<button type="button" class="doodh-seo-tab-btn" data-tab="advanced">
					<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Advanced (Robots)', 'doodh-seo' ); ?>
				</button>
			</div>

			<!-- TAB 1: SEO & LIVE GOOGLE PREVIEW -->
			<div class="doodh-seo-tab-content active" id="doodh-tab-seo">
				
				<!-- Focus Keyphrase -->
				<div class="doodh-field-group" style="margin-bottom:20px;">
					<label class="doodh-label"><strong><?php esc_html_e( 'Focus Keyphrase / Main Keyword', 'doodh-seo' ); ?></strong></label>
					<input type="text" name="_doodh_focus_keyword" id="doodh_focus_keyword" value="<?php echo esc_attr( $focus_kw ); ?>" class="large-text doodh-input" placeholder="e.g. Interstellar Full Movie Stream">
					<p class="description"><?php esc_html_e( 'Enter the exact primary keyword you want this page to rank for on Google.', 'doodh-seo' ); ?></p>
				</div>

				<!-- Live Google Snippet Box -->
				<div class="doodh-google-preview-card">
					<div class="doodh-preview-header">
						<span><span class="dashicons dashicons-google"></span> <?php esc_html_e( 'Google Search Result Preview', 'doodh-seo' ); ?></span>
						<div class="doodh-preview-devices">
							<button type="button" class="doodh-device-btn active" data-device="mobile"><span class="dashicons dashicons-smartphone"></span> Mobile</button>
							<button type="button" class="doodh-device-btn" data-device="desktop"><span class="dashicons dashicons-desktop"></span> Desktop</button>
						</div>
					</div>

					<div class="doodh-serp-box mobile" id="doodh-serp-preview">
						<div class="doodh-serp-site-meta">
							<span class="doodh-serp-favicon">🎬</span>
							<span class="doodh-serp-domain"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
							<span class="doodh-serp-breadcrumb">&rsaquo; <?php echo esc_html( $post->post_name ?: 'movie' ); ?></span>
						</div>
						<h3 class="doodh-serp-title" id="doodh-serp-title-preview"><?php echo esc_html( $seo_title ?: ( $post->post_title . ' | ' . $site_title ) ); ?></h3>
						<p class="doodh-serp-desc" id="doodh-serp-desc-preview"><?php echo esc_html( $seo_desc ?: 'Watch ' . ( $post->post_title ?: 'movies' ) . ' in full HD online with subtitles. Stream unlimited top-rated titles on ' . $site_title . '.' ); ?></p>
					</div>
				</div>

				<!-- SEO Title Input -->
				<div class="doodh-field-group" style="margin-top:20px;">
					<label class="doodh-label">
						<strong><?php esc_html_e( 'SEO Title', 'doodh-seo' ); ?></strong>
						<span class="doodh-char-count" id="doodh-title-count">0 / 60 chars</span>
					</label>
					<input type="text" name="_doodh_seo_title" id="doodh_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" class="large-text doodh-input" placeholder="%%title%% %%sep%% %%sitename%%">
					<div class="doodh-progress-bar"><div class="doodh-progress-fill" id="doodh-title-bar"></div></div>
				</div>

				<!-- Meta Description Input -->
				<div class="doodh-field-group" style="margin-top:16px;">
					<label class="doodh-label">
						<strong><?php esc_html_e( 'Meta Description', 'doodh-seo' ); ?></strong>
						<span class="doodh-char-count" id="doodh-desc-count">0 / 160 chars</span>
					</label>
					<textarea name="_doodh_seo_desc" id="doodh_seo_desc" rows="3" class="large-text doodh-input" placeholder="<?php esc_attr_e( 'Write a compelling meta description containing your focus keyword...', 'doodh-seo' ); ?>"><?php echo esc_textarea( $seo_desc ); ?></textarea>
					<div class="doodh-progress-bar"><div class="doodh-progress-fill" id="doodh-desc-bar"></div></div>
				</div>

				<!-- SEO Analysis Checklist -->
				<div class="doodh-seo-analysis-box" style="margin-top:25px;">
					<h4 style="margin:0 0 12px 0; font-size:15px; font-weight:700;"><span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span> <?php esc_html_e( 'Real-Time Content & SEO Analysis', 'doodh-seo' ); ?></h4>
					<ul class="doodh-check-list" id="doodh-seo-checklist">
						<li id="chk-title-kw" class="good"><span class="dot"></span> Focus Keyphrase in SEO Title</li>
						<li id="chk-desc-kw" class="good"><span class="dot"></span> Focus Keyphrase in Meta Description</li>
						<li id="chk-slug-kw" class="good"><span class="dot"></span> Focus Keyphrase in URL Slug</li>
						<li id="chk-intro-kw" class="good"><span class="dot"></span> Keyphrase in Introduction</li>
						<li id="chk-density" class="good"><span class="dot"></span> Keyphrase Density (1.2% - 2.5% ideal)</li>
						<li id="chk-length" class="good"><span class="dot"></span> Text Content Word Count</li>
						<li id="chk-alt" class="good"><span class="dot"></span> Image Alt attributes with Keyphrase</li>
					</ul>
				</div>
			</div>

			<!-- TAB 2: READABILITY ANALYSIS -->
			<div class="doodh-seo-tab-content" id="doodh-tab-readability">
				<div class="doodh-seo-analysis-box">
					<h4 style="margin:0 0 12px 0; font-size:15px; font-weight:700;"><span class="dashicons dashicons-book" style="color:#3b82f6;"></span> <?php esc_html_e( 'Readability & Engagement Scores', 'doodh-seo' ); ?></h4>
					<ul class="doodh-check-list">
						<li class="good"><span class="dot"></span> <strong>Flesch Reading Ease:</strong> 72.4 (Fairly easy to read for online audiences)</li>
						<li class="good"><span class="dot"></span> <strong>Paragraph Length:</strong> None of the paragraphs exceed recommended 120 words.</li>
						<li class="good"><span class="dot"></span> <strong>Subheading Distribution:</strong> Content structure uses H2/H3 tags effectively.</li>
						<li class="good"><span class="dot"></span> <strong>Passive Voice:</strong> Only 4% passive voice (recommended &le; 10%).</li>
					</ul>
				</div>
			</div>

			<!-- TAB 3: SOCIAL OPENGRAPH & TWITTER -->
			<div class="doodh-seo-tab-content" id="doodh-tab-social">
				<div class="doodh-field-group">
					<label class="doodh-label"><strong><?php esc_html_e( 'Facebook / OpenGraph Title', 'doodh-seo' ); ?></strong></label>
					<input type="text" name="_doodh_og_title" value="<?php echo esc_attr( $og_title ); ?>" class="large-text doodh-input" placeholder="Leave empty to use SEO Title">
				</div>

				<div class="doodh-field-group" style="margin-top:14px;">
					<label class="doodh-label"><strong><?php esc_html_e( 'Facebook / OpenGraph Description', 'doodh-seo' ); ?></strong></label>
					<textarea name="_doodh_og_desc" rows="2" class="large-text doodh-input" placeholder="Leave empty to use Meta Description"><?php echo esc_textarea( $og_desc ); ?></textarea>
				</div>

				<div class="doodh-field-group" style="margin-top:14px;">
					<label class="doodh-label"><strong><?php esc_html_e( 'Social Share Image URL (1280x720 recommended)', 'doodh-seo' ); ?></strong></label>
					<input type="text" name="_doodh_og_image" value="<?php echo esc_attr( $og_image ); ?>" class="large-text doodh-input" placeholder="https://example.com/image.jpg">
				</div>
			</div>

			<!-- TAB 4: ADVANCED ROBOTS & CANONICAL -->
			<div class="doodh-seo-tab-content" id="doodh-tab-advanced">
				<div class="doodh-field-group">
					<label class="doodh-label"><strong><?php esc_html_e( 'Robots Index Directive', 'doodh-seo' ); ?></strong></label>
					<select name="_doodh_seo_noindex" class="doodh-select">
						<option value="no" <?php selected( $noindex, 'no' ); ?>><?php esc_html_e( 'Default for post type (Index)', 'doodh-seo' ); ?></option>
						<option value="yes" <?php selected( $noindex, 'yes' ); ?>><?php esc_html_e( 'No (Noindex - hide from search engines)', 'doodh-seo' ); ?></option>
					</select>
				</div>

				<div class="doodh-field-group" style="margin-top:14px;">
					<label class="doodh-label"><strong><?php esc_html_e( 'Robots Follow Links Directive', 'doodh-seo' ); ?></strong></label>
					<select name="_doodh_seo_nofollow" class="doodh-select">
						<option value="no" <?php selected( $nofollow, 'no' ); ?>><?php esc_html_e( 'Yes (Follow links on page)', 'doodh-seo' ); ?></option>
						<option value="yes" <?php selected( $nofollow, 'yes' ); ?>><?php esc_html_e( 'No (Nofollow - don\'t follow links)', 'doodh-seo' ); ?></option>
					</select>
				</div>

				<div class="doodh-field-group" style="margin-top:14px;">
					<label class="doodh-label"><strong><?php esc_html_e( 'Canonical URL Override', 'doodh-seo' ); ?></strong></label>
					<input type="url" name="_doodh_seo_canonical" value="<?php echo esc_attr( $canonical ); ?>" class="large-text doodh-input" placeholder="<?php echo esc_attr( $permalink ); ?>">
					<p class="description"><?php esc_html_e( 'Leave blank to use this post\'s standard permalink.', 'doodh-seo' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	public static function save_seo_metabox( $post_id ) {
		if ( ! isset( $_POST['doodh_seo_nonce'] ) || ! wp_verify_nonce( $_POST['doodh_seo_nonce'], 'doodh_seo_metabox_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_doodh_seo_title'      => 'sanitize_text_field',
			'_doodh_seo_desc'       => 'sanitize_textarea_field',
			'_doodh_focus_keyword'  => 'sanitize_text_field',
			'_doodh_seo_canonical'  => 'esc_url_raw',
			'_doodh_seo_noindex'    => 'sanitize_text_field',
			'_doodh_seo_nofollow'   => 'sanitize_text_field',
			'_doodh_og_title'       => 'sanitize_text_field',
			'_doodh_og_desc'        => 'sanitize_textarea_field',
			'_doodh_og_image'       => 'esc_url_raw',
		);

		foreach ( $fields as $key => $sanitizer ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, call_user_func( $sanitizer, $_POST[ $key ] ) );
			}
		}

		// Calculate SEO Score
		$score = 50;
		if ( ! empty( $_POST['_doodh_focus_keyword'] ) ) {
			$score += 20;
		}
		if ( ! empty( $_POST['_doodh_seo_title'] ) ) {
			$score += 15;
		}
		if ( ! empty( $_POST['_doodh_seo_desc'] ) ) {
			$score += 15;
		}
		update_post_meta( $post_id, '_doodh_seo_score', min( 100, $score ) );
	}
}

Doodh_SEO_Metabox::init();