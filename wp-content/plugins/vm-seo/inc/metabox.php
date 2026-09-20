<?php
/**
 * VM SEO - Live Snippet Editor & Content Analyzer Metabox (Yoast-Grade)
 *
 * @package VMSEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VM_SEO_Metabox {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_seo_metabox' ) );
		add_action( 'save_post', array( __CLASS__, 'save_seo_metabox' ) );
	}

	public static function add_seo_metabox() {
		$post_types = array( 'post', 'page', 'movies', 'tvshows', 'episodes' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'vm_seo_master_box',
				'<span class="dashicons dashicons-search" style="color:#e50914; vertical-align:middle;"></span> ' . __( 'VM SEO — Live Snippet & Content Optimizer', 'vm-seo' ),
				array( __CLASS__, 'render_metabox' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	public static function render_metabox( $post ) {
		wp_nonce_field( 'vm_seo_metabox_save', 'vm_seo_nonce' );

		$seo_title    = get_post_meta( $post->ID, '_vm_seo_title', true );
		$seo_desc     = get_post_meta( $post->ID, '_vm_seo_desc', true );
		$focus_kw     = get_post_meta( $post->ID, '_vm_focus_keyword', true );
		$canonical    = get_post_meta( $post->ID, '_vm_seo_canonical', true );
		$noindex      = get_post_meta( $post->ID, '_vm_seo_noindex', true );
		$nofollow     = get_post_meta( $post->ID, '_vm_seo_nofollow', true );
		$schema_type  = get_post_meta( $post->ID, '_vm_schema_type', true );
		$og_title     = get_post_meta( $post->ID, '_vm_og_title', true );
		$og_desc      = get_post_meta( $post->ID, '_vm_og_desc', true );
		$og_image     = get_post_meta( $post->ID, '_vm_og_image', true );
		$seo_score    = (int) get_post_meta( $post->ID, '_vm_seo_score', true ) ?: 85;

		$permalink    = get_permalink( $post->ID ) ?: home_url( '/' . ( $post->post_name ?: 'sample-movie' ) . '/' );
		$site_title   = get_bloginfo( 'name' );

		if ( empty( $schema_type ) ) {
			$schema_type = ( $post->post_type === 'movies' ) ? 'Movie' : ( ( $post->post_type === 'tvshows' ) ? 'TVSeries' : 'Article' );
		}
		?>
		<div class="vm-seo-metabox-wrap">
			
			<!-- Tab Navigation Bar -->
			<div class="vm-seo-tabs-nav">
				<button type="button" class="vm-seo-tab-btn active" data-tab="seo">
					<span class="dashicons dashicons-search"></span> <?php esc_html_e( 'SEO & Snippet', 'vm-seo' ); ?>
					<span class="vm-score-pill-sm <?php echo ( $seo_score >= 80 ) ? 'good' : ( ( $seo_score >= 50 ) ? 'ok' : 'poor' ); ?>" id="vm-badge-score"><?php echo esc_html( $seo_score ); ?>/100</span>
				</button>
				<button type="button" class="vm-seo-tab-btn" data-tab="readability">
					<span class="dashicons dashicons-visibility"></span> <?php esc_html_e( 'Readability Analysis', 'vm-seo' ); ?>
					<span class="vm-score-pill-sm good" id="vm-readability-badge">Good</span>
				</button>
				<button type="button" class="vm-seo-tab-btn" data-tab="social">
					<span class="dashicons dashicons-share"></span> <?php esc_html_e( 'Social Previews (FB & X)', 'vm-seo' ); ?>
				</button>
				<button type="button" class="vm-seo-tab-btn" data-tab="schema">
					<span class="dashicons dashicons-networking"></span> <?php esc_html_e( 'Schema.org', 'vm-seo' ); ?>
				</button>
				<button type="button" class="vm-seo-tab-btn" data-tab="advanced">
					<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Advanced (Robots)', 'vm-seo' ); ?>
				</button>
			</div>

			<!-- TAB 1: SEO & LIVE GOOGLE PREVIEW -->
			<div class="vm-seo-tab-content active" id="vm-tab-seo">
				
				<!-- Focus Keyphrase -->
				<div class="vm-field-group" style="margin-bottom:18px;">
					<label class="vm-label">
						<strong><?php esc_html_e( 'Focus Keyphrase / Main Target Keyword', 'vm-seo' ); ?></strong>
						<span class="description" style="font-size:11px;"><?php esc_html_e( 'Search query you want to rank for', 'vm-seo' ); ?></span>
					</label>
					<input type="text" name="_vm_focus_keyword" id="vm_focus_keyword" value="<?php echo esc_attr( $focus_kw ); ?>" class="large-text vm-input" placeholder="e.g. Inception Full Movie Stream HD">
				</div>

				<!-- Live Google Snippet Box -->
				<div class="vm-google-preview-card">
					<div class="vm-preview-header">
						<span><span class="dashicons dashicons-google"></span> <?php esc_html_e( 'Live Google Search Snippet Preview', 'vm-seo' ); ?></span>
						<div class="vm-preview-devices">
							<button type="button" class="vm-device-btn active" data-device="mobile"><span class="dashicons dashicons-smartphone"></span> Mobile</button>
							<button type="button" class="vm-device-btn" data-device="desktop"><span class="dashicons dashicons-desktop"></span> Desktop</button>
						</div>
					</div>

					<div class="vm-serp-box mobile" id="vm-serp-preview">
						<div class="vm-serp-site-meta">
							<span class="vm-serp-favicon">🎬</span>
							<span class="vm-serp-domain"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
							<span class="vm-serp-breadcrumb">&rsaquo; <?php echo esc_html( $post->post_name ?: 'title' ); ?></span>
						</div>
						<h3 class="vm-serp-title" id="vm-serp-title-preview"><?php echo esc_html( $seo_title ?: ( $post->post_title . ' | ' . $site_title ) ); ?></h3>
						<p class="vm-serp-desc" id="vm-serp-desc-preview"><?php echo esc_html( $seo_desc ?: 'Watch ' . ( $post->post_title ?: 'movies' ) . ' in full HD online with subtitles. Stream unlimited titles on ' . $site_title . '.' ); ?></p>
					</div>
				</div>

				<!-- Variable Insertion Bar -->
				<div class="vm-var-pills-bar" style="margin:16px 0 8px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
					<strong style="font-size:12px; color:#475569; margin-right:4px;"><?php esc_html_e( 'Insert Variable:', 'vm-seo' ); ?></strong>
					<button type="button" class="vm-var-pill-btn" data-var="%%title%%">+ Title</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%year%%">+ Year</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%director%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Director</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%cast%%" style="background:#fef2f2; border-color:#fecaca; color:#dc2626; font-weight:700;">+ Cast/Actors</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%genres%%">+ Genre</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%quality%%">+ Quality</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%rating%%">+ Rating</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%sep%%">+ Separator</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%sitename%%">+ Site Name</button>
					<button type="button" class="vm-var-pill-btn" data-var="%%tagline%%">+ Tagline</button>
				</div>

				<!-- SEO Title Input -->
				<div class="vm-field-group" style="margin-top:10px;">
					<label class="vm-label">
						<strong><?php esc_html_e( 'SEO Title', 'vm-seo' ); ?></strong>
						<span class="vm-char-count" id="vm-title-count">0 / 60 chars</span>
					</label>
					<input type="text" name="_vm_seo_title" id="vm_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" class="large-text vm-input" placeholder="%%title%% (%%year%%) Full Movie HD Stream %%sep%% %%sitename%%">
					<div class="vm-progress-bar"><div class="vm-progress-fill" id="vm-title-bar"></div></div>
				</div>

				<!-- Meta Description Input -->
				<div class="vm-field-group" style="margin-top:16px;">
					<label class="vm-label">
						<strong><?php esc_html_e( 'Meta Description', 'vm-seo' ); ?></strong>
						<span class="vm-char-count" id="vm-desc-count">0 / 160 chars</span>
					</label>
					<textarea name="_vm_seo_desc" id="vm_seo_desc" rows="3" class="large-text vm-input" placeholder="<?php esc_attr_e( 'Write a compelling meta description containing your focus keyword to increase CTR on Google...', 'vm-seo' ); ?>"><?php echo esc_textarea( $seo_desc ); ?></textarea>
					<div class="vm-progress-bar"><div class="vm-progress-fill" id="vm-desc-bar"></div></div>
				</div>

				<!-- Live SEO Analysis Checklist -->
				<div class="vm-seo-analysis-box" style="margin-top:25px;">
					<h4 style="margin:0 0 14px 0; font-size:14px; font-weight:700; color:#0f172a; display:flex; align-items:center; justify-content:space-between;">
						<span><span class="dashicons dashicons-analytics" style="color:#2563eb; vertical-align:middle;"></span> <?php esc_html_e( 'Real-Time Content & SEO Analysis', 'vm-seo' ); ?></span>
						<span id="vm-seo-overall-badge" style="font-size:12px; font-weight:700; padding:2px 8px; border-radius:12px; background:#dcfce7; color:#15803d;">85/100 (Good)</span>
					</h4>
					<ul class="vm-check-list" id="vm-seo-checklist">
						<li id="chk-title-kw" class="good"><span class="dot"></span> <span class="chk-text">Focus Keyphrase in SEO Title</span></li>
						<li id="chk-title-start" class="good"><span class="dot"></span> <span class="chk-text">Keyphrase at beginning of SEO Title</span></li>
						<li id="chk-desc-kw" class="good"><span class="dot"></span> <span class="chk-text">Focus Keyphrase in Meta Description</span></li>
						<li id="chk-desc-length" class="good"><span class="dot"></span> <span class="chk-text">Meta Description length is optimal</span></li>
						<li id="chk-slug-kw" class="good"><span class="dot"></span> <span class="chk-text">Focus Keyphrase in URL Slug</span></li>
						<li id="chk-intro-kw" class="good"><span class="dot"></span> <span class="chk-text">Keyphrase appears in Introduction</span></li>
						<li id="chk-density" class="good"><span class="dot"></span> <span class="chk-text">Keyphrase Density: <strong id="vm-calc-density">1.4%</strong> (Recommended: 1.0% - 2.5%)</span></li>
						<li id="chk-length" class="good"><span class="dot"></span> <span class="chk-text">Content Word Count: <strong id="vm-calc-words">0 words</strong></span></li>
						<li id="chk-headings" class="good"><span class="dot"></span> <span class="chk-text">Subheadings (H2, H3) usage in content</span></li>
						<li id="chk-alt" class="good"><span class="dot"></span> <span class="chk-text">Image Alt attributes contain Keyphrase</span></li>
					</ul>
				</div>
			</div>

			<!-- TAB 2: READABILITY ANALYSIS -->
			<div class="vm-seo-tab-content" id="vm-tab-readability">
				<div class="vm-seo-analysis-box">
					<h4 style="margin:0 0 14px 0; font-size:14px; font-weight:700; color:#0f172a;">
						<span class="dashicons dashicons-book" style="color:#3b82f6; vertical-align:middle;"></span> <?php esc_html_e( 'Readability & Audience Engagement Evaluation', 'vm-seo' ); ?>
					</h4>
					<ul class="vm-check-list" id="vm-readability-checklist">
						<li id="rd-flesch" class="good"><span class="dot"></span> <strong>Flesch Reading Ease:</strong> <span id="vm-flesch-score">75.2</span> (Easily understood by casual readers)</li>
						<li id="rd-paragraphs" class="good"><span class="dot"></span> <strong>Paragraph Length:</strong> Concise and scannable for mobile screens.</li>
						<li id="rd-sentences" class="good"><span class="dot"></span> <strong>Sentence Length:</strong> Most sentences are under 20 words.</li>
						<li id="rd-passive" class="good"><span class="dot"></span> <strong>Passive Voice:</strong> Low passive voice usage (&le; 10%).</li>
						<li id="rd-transitions" class="good"><span class="dot"></span> <strong>Transition Words:</strong> Good flow and structural cohesion.</li>
						<li id="rd-headings" class="good"><span class="dot"></span> <strong>Subheading Distribution:</strong> Content is well structured with clear sections.</li>
					</ul>
				</div>
			</div>

			<!-- TAB 3: SOCIAL OPENGRAPH & TWITTER PREVIEWS -->
			<div class="vm-seo-tab-content" id="vm-tab-social">
				
				<!-- Facebook Card Preview -->
				<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:20px;">
					<h4 style="margin:0 0 10px 0; font-size:13px; color:#1877f2;"><span class="dashicons dashicons-facebook"></span> Facebook / OpenGraph Card Preview</h4>
					<div class="vm-social-preview-box" id="vm-fb-preview" style="max-width:500px; background:#fff; border:1px solid #ddd; border-radius:8px; overflow:hidden;">
						<div id="vm-fb-img-prev" style="height:200px; background:#0f172a; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:12px; background-size:cover; background-position:center;">
							<span>No Social Image Selected</span>
						</div>
						<div style="padding:12px;">
							<div style="font-size:11px; text-transform:uppercase; color:#65676b;" id="vm-fb-domain"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></div>
							<div style="font-weight:700; font-size:14px; color:#050505; margin:4px 0;" id="vm-fb-title-prev"><?php echo esc_html( $og_title ?: ( $seo_title ?: $post->post_title ) ); ?></div>
							<div style="font-size:12px; color:#65676b; line-height:1.3;" id="vm-fb-desc-prev"><?php echo esc_html( $og_desc ?: ( $seo_desc ?: 'Watch online in HD.' ) ); ?></div>
						</div>
					</div>
				</div>

				<div class="vm-field-group">
					<label class="vm-label"><strong><?php esc_html_e( 'Social Share Title (Facebook & X)', 'vm-seo' ); ?></strong></label>
					<input type="text" name="_vm_og_title" id="vm_og_title" value="<?php echo esc_attr( $og_title ); ?>" class="large-text vm-input" placeholder="Leave blank to use SEO Title">
				</div>

				<div class="vm-field-group" style="margin-top:14px;">
					<label class="vm-label"><strong><?php esc_html_e( 'Social Share Description', 'vm-seo' ); ?></strong></label>
					<textarea name="_vm_og_desc" id="vm_og_desc" rows="2" class="large-text vm-input" placeholder="Leave blank to use Meta Description"><?php echo esc_textarea( $og_desc ); ?></textarea>
				</div>

				<div class="vm-field-group" style="margin-top:14px;">
					<label class="vm-label"><strong><?php esc_html_e( 'Social Share Image URL (1280x720 recommended)', 'vm-seo' ); ?></strong></label>
					<div style="display:flex; gap:8px;">
						<input type="text" name="_vm_og_image" id="vm_og_image" value="<?php echo esc_attr( $og_image ); ?>" class="large-text vm-input" placeholder="https://example.com/backdrop.jpg" style="flex:1;">
						<button type="button" id="vm-btn-pick-og-image" class="button button-secondary"><?php esc_html_e( 'Select Image', 'vm-seo' ); ?></button>
					</div>
				</div>
			</div>

			<!-- TAB 4: SCHEMA.ORG JSON-LD -->
			<div class="vm-seo-tab-content" id="vm-tab-schema">
				<div class="vm-field-group">
					<label class="vm-label"><strong><?php esc_html_e( 'Schema.org Rich Snippet Type', 'vm-seo' ); ?></strong></label>
					<select name="_vm_schema_type" class="vm-select" style="width:100%; max-width:400px;">
						<option value="Movie" <?php selected( $schema_type, 'Movie' ); ?>>🎬 Movie (with Director, Cast & Rating)</option>
						<option value="TVSeries" <?php selected( $schema_type, 'TVSeries' ); ?>>📺 TV Series (with Seasons, Episodes & Rating)</option>
						<option value="TVEpisode" <?php selected( $schema_type, 'TVEpisode' ); ?>>🎞️ TV Episode</option>
						<option value="Article" <?php selected( $schema_type, 'Article' ); ?>>📝 News / Article</option>
						<option value="WebPage" <?php selected( $schema_type, 'WebPage' ); ?>>📄 Standard WebPage</option>
					</select>
					<p class="description"><?php esc_html_e( 'VM SEO automatically builds the JSON-LD graph with AggregateRating, actors, release dates, and breadcrumbs.', 'vm-seo' ); ?></p>
				</div>
			</div>

			<!-- TAB 5: ADVANCED (ROBOTS & CANONICAL) -->
			<div class="vm-seo-tab-content" id="vm-tab-advanced">
				<div class="vm-field-group">
					<label class="vm-label"><strong><?php esc_html_e( 'Search Engine Indexing (Robots Index)', 'vm-seo' ); ?></strong></label>
					<select name="_vm_seo_noindex" class="vm-select" style="width:100%; max-width:400px;">
						<option value="no" <?php selected( $noindex, 'no' ); ?>><?php esc_html_e( 'Yes (Index - Allow in search results)', 'vm-seo' ); ?></option>
						<option value="yes" <?php selected( $noindex, 'yes' ); ?>><?php esc_html_e( 'No (Noindex - Hide from search results)', 'vm-seo' ); ?></option>
					</select>
				</div>

				<div class="vm-field-group" style="margin-top:14px;">
					<label class="vm-label"><strong><?php esc_html_e( 'Follow Links on this Page (Robots Follow)', 'vm-seo' ); ?></strong></label>
					<select name="_vm_seo_nofollow" class="vm-select" style="width:100%; max-width:400px;">
						<option value="no" <?php selected( $nofollow, 'no' ); ?>><?php esc_html_e( 'Yes (Follow links on page)', 'vm-seo' ); ?></option>
						<option value="yes" <?php selected( $nofollow, 'yes' ); ?>><?php esc_html_e( 'No (Nofollow - Do not follow links)', 'vm-seo' ); ?></option>
					</select>
				</div>

				<div class="vm-field-group" style="margin-top:14px;">
					<label class="vm-label"><strong><?php esc_html_e( 'Canonical URL Override', 'vm-seo' ); ?></strong></label>
					<input type="url" name="_vm_seo_canonical" value="<?php echo esc_attr( $canonical ); ?>" class="large-text vm-input" placeholder="<?php echo esc_attr( $permalink ); ?>">
					<p class="description"><?php esc_html_e( 'Leave blank to automatically use this title\'s canonical permalink.', 'vm-seo' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	public static function save_seo_metabox( $post_id ) {
		if ( ! isset( $_POST['vm_seo_nonce'] ) || ! wp_verify_nonce( $_POST['vm_seo_nonce'], 'vm_seo_metabox_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_vm_seo_title'      => 'sanitize_text_field',
			'_vm_seo_desc'       => 'sanitize_textarea_field',
			'_vm_focus_keyword'  => 'sanitize_text_field',
			'_vm_seo_canonical'  => 'esc_url_raw',
			'_vm_seo_noindex'    => 'sanitize_text_field',
			'_vm_seo_nofollow'   => 'sanitize_text_field',
			'_vm_schema_type'    => 'sanitize_text_field',
			'_vm_og_title'       => 'sanitize_text_field',
			'_vm_og_desc'        => 'sanitize_textarea_field',
			'_vm_og_image'       => 'esc_url_raw',
		);

		foreach ( $fields as $key => $sanitizer ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, call_user_func( $sanitizer, $_POST[ $key ] ) );
			}
		}

		// Calculate Comprehensive SEO Score
		$score = 40;
		$kw = trim( $_POST['_vm_focus_keyword'] ?? '' );
		$title = trim( $_POST['_vm_seo_title'] ?? '' );
		$desc = trim( $_POST['_vm_seo_desc'] ?? '' );

		if ( ! empty( $kw ) ) {
			$score += 20;
			if ( ! empty( $title ) && stripos( $title, $kw ) !== false ) {
				$score += 15;
			}
			if ( ! empty( $desc ) && stripos( $desc, $kw ) !== false ) {
				$score += 15;
			}
		}
		if ( strlen( $title ) >= 35 && strlen( $title ) <= 65 ) {
			$score += 5;
		}
		if ( strlen( $desc ) >= 110 && strlen( $desc ) <= 165 ) {
			$score += 5;
		}

		update_post_meta( $post_id, '_vm_seo_score', min( 100, max( 10, $score ) ) );
	}
}

VM_SEO_Metabox::init();