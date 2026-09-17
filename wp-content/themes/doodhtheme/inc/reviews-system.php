<?php
/**
 * Star Reviews & Community Rating System with Dedicated Reviewer Profile Pages
 * Multi-Review Admin Management, TMDb & IMDb Deduplication, Frontend Dynamic Display, and SEO Schema Integration
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Admin Menus for Reviews Management under Posts, Movies, and TV Shows
 */
function doodhtheme_register_reviews_admin_menus() {
	add_submenu_page(
		'edit.php',
		__( 'Reviews & Ratings', 'vmtheme' ),
		__( 'Reviews & Ratings', 'vmtheme' ),
		'edit_posts',
		'doodhtheme-post-reviews',
		'doodhtheme_render_admin_reviews_page'
	);

	add_submenu_page(
		'edit.php?post_type=movies',
		__( 'Movie Reviews', 'vmtheme' ),
		__( 'Reviews & Ratings', 'vmtheme' ),
		'edit_posts',
		'doodhtheme-movie-reviews',
		'doodhtheme_render_admin_reviews_page'
	);

	add_submenu_page(
		'edit.php?post_type=tvshows',
		__( 'TV Show Reviews', 'vmtheme' ),
		__( 'Reviews & Ratings', 'vmtheme' ),
		'edit_posts',
		'doodhtheme-tv-reviews',
		'doodhtheme_render_admin_reviews_page'
	);
}
add_action( 'admin_menu', 'doodhtheme_register_reviews_admin_menus' );

/**
 * Create or Retrieve a Reviewer / Critic Profile Term in dtreviewer taxonomy
 *
 * @param string $name Reviewer name
 * @param string $avatar Avatar image URL
 * @param string $badge Badge key ('tmdb_critic', 'imdb_critic', 'verified_critic', 'editorial', 'community')
 * @param string $site Website URL
 * @param string $bio Short bio
 * @return array|false Term details array
 */
function doodhtheme_get_or_create_reviewer_term( $name, $avatar = '', $badge = 'verified_critic', $site = '', $bio = '' ) {
	$name = trim( sanitize_text_field( $name ) );
	if ( empty( $name ) ) {
		$name = __( 'Verified Critic', 'vmtheme' );
	}

	$term = term_exists( $name, 'dtreviewer' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'dtreviewer', array(
			'description' => $bio,
		) );
	}

	if ( is_wp_error( $term ) || ! $term ) {
		return false;
	}

	$term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;

	// Update term meta if provided and not yet set
	if ( ! empty( $avatar ) ) {
		$existing_avatar = get_term_meta( $term_id, '_dt_reviewer_avatar', true );
		if ( empty( $existing_avatar ) || strpos( $existing_avatar, 'placeholder' ) !== false ) {
			update_term_meta( $term_id, '_dt_reviewer_avatar', esc_url_raw( $avatar ) );
		}
	}
	if ( ! empty( $badge ) ) {
		$existing_badge = get_term_meta( $term_id, '_dt_reviewer_badge', true );
		if ( empty( $existing_badge ) ) {
			update_term_meta( $term_id, '_dt_reviewer_badge', sanitize_text_field( $badge ) );
		}
	}
	if ( ! empty( $site ) ) {
		$existing_site = get_term_meta( $term_id, '_dt_reviewer_site', true );
		if ( empty( $existing_site ) ) {
			update_term_meta( $term_id, '_dt_reviewer_site', esc_url_raw( $site ) );
		}
	}

	$term_link = get_term_link( $term_id, 'dtreviewer' );

	return array(
		'term_id' => $term_id,
		'name'    => $name,
		'url'     => ! is_wp_error( $term_link ) ? $term_link : '#',
		'avatar'  => get_term_meta( $term_id, '_dt_reviewer_avatar', true ) ?: $avatar,
		'badge'   => get_term_meta( $term_id, '_dt_reviewer_badge', true ) ?: $badge,
	);
}

/**
 * Strict Duplicate Review Check
 * Prevents importing the same TMDb or IMDb review multiple times
 *
 * @param int $post_id Post ID
 * @param string $author Review author name
 * @param string $content Review content text
 * @param string $tmdb_id Optional TMDb review ID
 * @param string $imdb_id Optional IMDb review ID
 * @return bool True if review is duplicate, false otherwise
 */
function doodhtheme_review_is_duplicate( $post_id, $author, $content, $tmdb_id = '', $imdb_id = '' ) {
	global $wpdb;

	// 1. Check by TMDb Review ID
	if ( ! empty( $tmdb_id ) ) {
		$exists_tmdb = $wpdb->get_var( $wpdb->prepare(
			"SELECT c.comment_ID FROM {$wpdb->comments} c
			 INNER JOIN {$wpdb->commentmeta} cm ON (c.comment_ID = cm.comment_id AND cm.meta_key = '_doodh_tmdb_review_id' AND cm.meta_value = %s)
			 WHERE c.comment_post_ID = %d LIMIT 1",
			$tmdb_id,
			$post_id
		) );
		if ( $exists_tmdb ) {
			return true;
		}
	}

	// 2. Check by IMDb Review ID
	if ( ! empty( $imdb_id ) ) {
		$exists_imdb = $wpdb->get_var( $wpdb->prepare(
			"SELECT c.comment_ID FROM {$wpdb->comments} c
			 INNER JOIN {$wpdb->commentmeta} cm ON (c.comment_ID = cm.comment_id AND cm.meta_key = '_doodh_imdb_review_id' AND cm.meta_value = %s)
			 WHERE c.comment_post_ID = %d LIMIT 1",
			$imdb_id,
			$post_id
		) );
		if ( $exists_imdb ) {
			return true;
		}
	}

	// 3. Check by Content Signature Hash
	$clean_author  = strtolower( trim( sanitize_text_field( $author ) ) );
	$clean_content = strtolower( trim( strip_tags( $content ) ) );
	$hash          = md5( $clean_author . '|' . $clean_content );

	$exists_hash = $wpdb->get_var( $wpdb->prepare(
		"SELECT c.comment_ID FROM {$wpdb->comments} c
		 INNER JOIN {$wpdb->commentmeta} cm ON (c.comment_ID = cm.comment_id AND cm.meta_key = '_doodh_review_hash' AND cm.meta_value = %s)
		 WHERE c.comment_post_ID = %d LIMIT 1",
		$hash,
		$post_id
	) );
	if ( $exists_hash ) {
		return true;
	}

	// 4. Check legacy _doodh_custom_reviews meta
	$custom_reviews = get_post_meta( $post_id, '_doodh_custom_reviews', true );
	if ( is_array( $custom_reviews ) && ! empty( $custom_reviews ) ) {
		foreach ( $custom_reviews as $cr ) {
			$cr_author  = strtolower( trim( $cr['author'] ?? '' ) );
			$cr_content = strtolower( trim( strip_tags( $cr['content'] ?? '' ) ) );
			if ( $cr_author === $clean_author || ( ! empty( $clean_content ) && $cr_content === $clean_content ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Cleanly Ingest & Store an Imported Review from TMDb / IMDb without Duplication
 *
 * @param int $post_id Post ID
 * @param array $data Review data array
 * @return int|false Comment ID or false if skipped/error
 */
function doodhtheme_insert_imported_review( $post_id, $data ) {
	$author   = sanitize_text_field( $data['author'] ?? 'Verified Critic' );
	$content  = wp_kses_post( $data['content'] ?? '' );
	$rating   = min( 10, max( 1, (int) ( $data['rating'] ?? 9 ) ) );
	$title    = sanitize_text_field( $data['title'] ?? ( mb_substr( strip_tags( $content ), 0, 60 ) . '...' ) );
	$date     = ! empty( $data['date'] ) ? date( 'Y-m-d H:i:s', strtotime( $data['date'] ) ) : current_time( 'mysql' );
	$avatar   = esc_url_raw( $data['avatar'] ?? '' );
	$source   = sanitize_text_field( $data['source'] ?? 'tmdb' ); // 'tmdb' or 'imdb' or 'critic'
	$tmdb_id  = sanitize_text_field( $data['tmdb_review_id'] ?? '' );
	$imdb_id  = sanitize_text_field( $data['imdb_review_id'] ?? '' );
	$site     = esc_url_raw( $data['site'] ?? '' );
	$bio      = sanitize_textarea_field( $data['bio'] ?? '' );

	if ( empty( $content ) ) {
		return false;
	}

	// 1. Strict Duplicate Check
	if ( doodhtheme_review_is_duplicate( $post_id, $author, $content, $tmdb_id, $imdb_id ) ) {
		return false; // Skip duplicate!
	}

	// 2. Determine badge
	$badge_type = 'verified_critic';
	if ( $source === 'tmdb' ) {
		$badge_type = 'tmdb_critic';
	} elseif ( $source === 'imdb' ) {
		$badge_type = 'imdb_critic';
	}

	// 3. Create or retrieve dedicated reviewer profile
	$reviewer_info = doodhtheme_get_or_create_reviewer_term( $author, $avatar, $badge_type, $site, $bio );
	$term_id = $reviewer_info ? (int) $reviewer_info['term_id'] : 0;

	// Attach reviewer taxonomy term to the post
	if ( $term_id ) {
		wp_set_object_terms( $post_id, array( $term_id ), 'dtreviewer', true );
	}

	$hash = md5( strtolower( trim( $author ) ) . '|' . strtolower( trim( strip_tags( $content ) ) ) );

	// 4. Insert comment
	$comment_id = wp_insert_comment( array(
		'comment_post_ID'      => $post_id,
		'comment_author'       => $author,
		'comment_author_email' => $source . '_critic@doodhtheme.internal',
		'comment_content'      => $content,
		'comment_type'         => 'comment',
		'comment_approved'     => 1,
		'comment_date'         => $date,
		'comment_date_gmt'     => get_gmt_from_date( $date ),
	) );

	if ( $comment_id && ! is_wp_error( $comment_id ) ) {
		update_comment_meta( $comment_id, '_doodh_review_rating', $rating );
		update_comment_meta( $comment_id, '_doodh_review_title', $title );
		update_comment_meta( $comment_id, '_doodh_review_source', $source );
		update_comment_meta( $comment_id, '_doodh_review_hash', $hash );
		update_comment_meta( $comment_id, '_doodh_verified_critic', 'yes' );
		update_comment_meta( $comment_id, '_doodh_reviewer_name', $author );
		if ( $term_id ) {
			update_comment_meta( $comment_id, '_doodh_reviewer_term_id', $term_id );
		}
		if ( $tmdb_id ) {
			update_comment_meta( $comment_id, '_doodh_tmdb_review_id', $tmdb_id );
		}
		if ( $imdb_id ) {
			update_comment_meta( $comment_id, '_doodh_imdb_review_id', $imdb_id );
		}
		if ( $avatar ) {
			update_comment_meta( $comment_id, '_doodh_reviewer_avatar', $avatar );
		}

		doodhtheme_update_aggregate_user_rating( $post_id );
		return $comment_id;
	}

	return false;
}

/**
 * Render Critic Badge HTML
 */
function doodhtheme_get_reviewer_badge_html( $badge_type = 'verified_critic' ) {
	switch ( $badge_type ) {
		case 'tmdb_critic':
			return '<span class="doodh-verified-badge doodh-badge-tmdb" style="background:rgba(1,180,228,0.15); color:#01b4e4; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-check-circle"></i> ' . esc_html__( 'TMDb Verified', 'vmtheme' ) . '</span>';
		case 'imdb_critic':
			return '<span class="doodh-verified-badge doodh-badge-imdb" style="background:rgba(245,197,24,0.18); color:#f5c518; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i class="fab fa-imdb"></i> ' . esc_html__( 'IMDb Top Critic', 'vmtheme' ) . '</span>';
		case 'editorial':
			return '<span class="doodh-verified-badge doodh-badge-editorial" style="background:rgba(229,9,20,0.15); color:#e50914; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-shield-alt"></i> ' . esc_html__( 'Staff Critic', 'vmtheme' ) . '</span>';
		default:
			return '<span class="doodh-verified-badge" style="background:rgba(16,185,129,0.15); color:#10b981; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px; display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-check-circle"></i> ' . esc_html__( 'Verified Reviewer', 'vmtheme' ) . '</span>';
	}
}

/**
 * Save custom fields when review is posted via frontend form
 */
function doodhtheme_save_comment_review_meta( $comment_id ) {
	if ( isset( $_POST['doodh_rating'] ) ) {
		$rating = min( 10, max( 1, (int) $_POST['doodh_rating'] ) );
		update_comment_meta( $comment_id, '_doodh_review_rating', $rating );

		$comment = get_comment( $comment_id );
		if ( $comment && $comment->comment_post_ID ) {
			$author = $comment->comment_author;
			$avatar = get_avatar_url( $comment->comment_author_email, array( 'size' => 120 ) );

			// Auto create / attach reviewer profile for user
			$rev_info = doodhtheme_get_or_create_reviewer_term( $author, $avatar, 'community' );
			if ( $rev_info && ! empty( $rev_info['term_id'] ) ) {
				update_comment_meta( $comment_id, '_doodh_reviewer_term_id', $rev_info['term_id'] );
				wp_set_object_terms( $comment->comment_post_ID, array( $rev_info['term_id'] ), 'dtreviewer', true );
			}

			$hash = md5( strtolower( trim( $author ) ) . '|' . strtolower( trim( strip_tags( $comment->comment_content ) ) ) );
			update_comment_meta( $comment_id, '_doodh_review_hash', $hash );
			update_comment_meta( $comment_id, '_doodh_review_source', 'community' );
			update_comment_meta( $comment_id, '_doodh_reviewer_name', $author );

			doodhtheme_update_aggregate_user_rating( $comment->comment_post_ID );
		}
	}

	if ( isset( $_POST['doodh_review_title'] ) ) {
		$title = sanitize_text_field( $_POST['doodh_review_title'] );
		update_comment_meta( $comment_id, '_doodh_review_title', $title );
	}
}
add_action( 'comment_post', 'doodhtheme_save_comment_review_meta' );

/**
 * Retrieve all Deduplicated post reviews with Reviewer Profile Page links
 *
 * @param int|null $post_id Post ID
 * @return array List of unique reviews
 */
function doodhtheme_get_all_post_reviews( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$all_reviews = array();
	$seen_hashes = array();

	// 1. Approved User & Imported Comments (Primary single source of truth)
	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'order'   => 'DESC',
	) );

	if ( ! empty( $comments ) ) {
		foreach ( $comments as $c ) {
			$author  = $c->comment_author ?: __( 'Verified Critic', 'vmtheme' );
			$content = $c->comment_content;
			$hash    = md5( strtolower( trim( $author ) ) . '|' . strtolower( trim( strip_tags( $content ) ) ) );

			if ( isset( $seen_hashes[ $hash ] ) ) {
				continue; // Skip duplicate comment!
			}
			$seen_hashes[ $hash ] = true;

			$rating  = get_comment_meta( $c->comment_ID, '_doodh_review_rating', true );
			$rating  = $rating ? min( 10, max( 1, (int) $rating ) ) : 9;
			$title   = get_comment_meta( $c->comment_ID, '_doodh_review_title', true );
			$source  = get_comment_meta( $c->comment_ID, '_doodh_review_source', true ) ?: 'community';
			$term_id = (int) get_comment_meta( $c->comment_ID, '_doodh_reviewer_term_id', true );
			$avatar  = get_comment_meta( $c->comment_ID, '_doodh_reviewer_avatar', true );

			$reviewer_url = '';
			$badge_type   = ( $source === 'tmdb' ) ? 'tmdb_critic' : ( ( $source === 'imdb' ) ? 'imdb_critic' : 'verified_critic' );

			if ( $term_id ) {
				$term_obj = get_term( $term_id, 'dtreviewer' );
				if ( $term_obj && ! is_wp_error( $term_obj ) ) {
					$reviewer_url = get_term_link( $term_obj );
					$badge_type   = get_term_meta( $term_id, '_dt_reviewer_badge', true ) ?: $badge_type;
					$t_avatar     = get_term_meta( $term_id, '_dt_reviewer_avatar', true );
					if ( ! empty( $t_avatar ) ) {
						$avatar = $t_avatar;
					}
				}
			} else {
				$term_obj = get_term_by( 'name', $author, 'dtreviewer' );
				if ( $term_obj && ! is_wp_error( $term_obj ) ) {
					$reviewer_url = get_term_link( $term_obj );
					$badge_type   = get_term_meta( $term_obj->term_id, '_dt_reviewer_badge', true ) ?: $badge_type;
					$t_avatar     = get_term_meta( $term_obj->term_id, '_dt_reviewer_avatar', true );
					if ( ! empty( $t_avatar ) ) {
						$avatar = $t_avatar;
					}
				}
			}

			if ( empty( $avatar ) ) {
				$avatar = get_avatar_url( $c->comment_author_email, array( 'size' => 64 ) );
			}

			$rev_link = get_comment_meta( $c->comment_ID, '_doodh_review_url', true ) ?: ( get_comment_meta( $c->comment_ID, 'review_url', true ) ?: '' );

			$all_reviews[] = array(
				'author'       => $author,
				'rating'       => $rating,
				'title'        => $title ?: '',
				'content'      => $content,
				'date'         => get_comment_date( 'Y-m-d', $c ),
				'verified'     => true,
				'avatar'       => $avatar ?: doodhtheme_get_fallback_avatar_url(),
				'source'       => $source,
				'badge_type'   => $badge_type,
				'reviewer_url' => ! is_wp_error( $reviewer_url ) && ! empty( $reviewer_url ) ? $reviewer_url : '',
				'review_url'   => ! empty( $rev_link ) ? esc_url_raw( $rev_link ) : '',
				'is_curated'   => ( $source !== 'community' ),
				'comment_id'   => $c->comment_ID,
			);
		}
	}

	// 2. Curated Reviews from Post Meta (Legacy Repeater) - Deduplicated!
	$custom_reviews = get_post_meta( $post_id, '_doodh_custom_reviews', true );
	if ( is_array( $custom_reviews ) && ! empty( $custom_reviews ) ) {
		foreach ( $custom_reviews as $cr ) {
			$author  = ! empty( $cr['author'] ) ? $cr['author'] : __( 'Verified Critic', 'vmtheme' );
			$content = ! empty( $cr['content'] ) ? $cr['content'] : '';
			$hash    = md5( strtolower( trim( $author ) ) . '|' . strtolower( trim( strip_tags( $content ) ) ) );

			if ( isset( $seen_hashes[ $hash ] ) ) {
				continue; // Skip duplicate!
			}
			$seen_hashes[ $hash ] = true;

			$term_obj = get_term_by( 'name', $author, 'dtreviewer' );
			$reviewer_url = ( $term_obj && ! is_wp_error( $term_obj ) ) ? get_term_link( $term_obj ) : '';
			$rev_link     = ! empty( $cr['review_url'] ) ? esc_url_raw( $cr['review_url'] ) : '';

			$all_reviews[] = array(
				'author'       => $author,
				'rating'       => min( 10, max( 1, (int) ( $cr['rating'] ?? 9 ) ) ),
				'title'        => ! empty( $cr['title'] ) ? $cr['title'] : '',
				'content'      => $content,
				'date'         => ! empty( $cr['date'] ) ? $cr['date'] : get_the_date( 'Y-m-d', $post_id ),
				'verified'     => ! empty( $cr['verified'] ),
				'avatar'       => ! empty( $cr['avatar'] ) ? $cr['avatar'] : doodhtheme_get_fallback_avatar_url(),
				'source'       => 'critic',
				'badge_type'   => 'verified_critic',
				'reviewer_url' => ! is_wp_error( $reviewer_url ) && ! empty( $reviewer_url ) ? $reviewer_url : '',
				'review_url'   => $rev_link,
				'is_curated'   => true,
			);
		}
	}

	return $all_reviews;
}

/**
 * Calculate full review metrics (average score, count, and star breakdown percentages)
 */
function doodhtheme_get_reviews_metrics( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$reviews = doodhtheme_get_all_post_reviews( $post_id );
	$total   = count( $reviews );

	$distribution = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
	$total_score  = 0;

	if ( $total > 0 ) {
		foreach ( $reviews as $r ) {
			$score = (int) $r['rating'];
			$total_score += $score;

			// Map 1-10 to 1-5 stars
			if ( $score >= 9 ) {
				$distribution[5]++;
			} elseif ( $score >= 7 ) {
				$distribution[4]++;
			} elseif ( $score >= 5 ) {
				$distribution[3]++;
			} elseif ( $score >= 3 ) {
				$distribution[2]++;
			} else {
				$distribution[1]++;
			}
		}

		$avg = round( $total_score / $total, 1 );
	} else {
		$default_rating = doodhtheme_get_rating( $post_id );
		$avg            = $default_rating ? (float) $default_rating : 7.8;
		$distribution[5] = 80;
		$distribution[4] = 15;
		$distribution[3] = 5;
	}

	$breakdown_percentages = array();
	foreach ( $distribution as $star => $count ) {
		$breakdown_percentages[ $star ] = ( $total > 0 ) ? round( ( $count / $total ) * 100 ) : $count;
	}

	return array(
		'avg_rating'  => number_format( (float) $avg, 1 ),
		'total_count' => $total ?: ( (int) doodhtheme_get_votes( $post_id ) ?: 18 ),
		'real_count'  => $total,
		'breakdown'   => $breakdown_percentages,
		'reviews'     => $reviews,
	);
}

/**
 * Recalculate and update aggregate user rating on a post
 */
function doodhtheme_update_aggregate_user_rating( $post_id ) {
	$metrics = doodhtheme_get_reviews_metrics( $post_id );
	update_post_meta( $post_id, '_doodh_user_rating_avg', $metrics['avg_rating'] );
	update_post_meta( $post_id, '_doodh_user_rating_count', $metrics['total_count'] );
}

/**
 * Render Complete Interactive Reviews & Star Rating Section with Advanced Column-Wise Responsive Layout
 */
function doodhtheme_render_reviews_section( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$metrics    = doodhtheme_get_reviews_metrics( $post_id );
	$reviews    = $metrics['reviews'];
	$avg        = (float) $metrics['avg_rating'];
	$total      = $metrics['total_count'];
	$real_count = $metrics['real_count'];
	$bd         = $metrics['breakdown'];

	// Calculate sub-counts for instant client filtering
	$critic_count    = 0;
	$community_count = 0;
	$positive_count  = 0;
	$star_counts     = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );

	if ( ! empty( $reviews ) ) {
		foreach ( $reviews as $r ) {
			$score = (int) ( $r['rating'] ?? 9 );
			if ( $score >= 7 ) {
				$positive_count++;
			}
			if ( ! empty( $r['is_curated'] ) || in_array( $r['badge_type'] ?? '', array( 'tmdb_critic', 'imdb_critic', 'editorial', 'verified_critic' ), true ) ) {
				$critic_count++;
			} else {
				$community_count++;
			}

			if ( $score >= 9 ) {
				$star_counts[5]++;
			} elseif ( $score >= 7 ) {
				$star_counts[4]++;
			} elseif ( $score >= 5 ) {
				$star_counts[3]++;
			} elseif ( $score >= 3 ) {
				$star_counts[2]++;
			} else {
				$star_counts[1]++;
			}
		}
	}

	$rec_pct = ( $real_count > 0 ) ? round( ( $positive_count / $real_count ) * 100 ) : 94;
	$overall_review_url = get_post_meta( $post_id, '_doodh_review_url', true ) ?: get_post_meta( $post_id, 'review_url', true );
	?>
	<section class="doodh-reviews-module" id="doodh-reviews-box" data-total-reviews="<?php echo esc_attr( $real_count ); ?>">
		<!-- Reviews Header Banner -->
		<div class="doodh-reviews-header">
			<div class="doodh-reviews-header-left">
				<div class="doodh-reviews-icon-box">
					<i class="fas fa-star"></i>
				</div>
				<div>
					<h3 class="doodh-reviews-main-title"><?php esc_html_e( 'Ratings & Reviews', 'vmtheme' ); ?></h3>
					<p class="doodh-reviews-sub-title"><?php esc_html_e( 'Verified critic critiques and community audience reactions', 'vmtheme' ); ?></p>
				</div>
			</div>
			<div class="doodh-reviews-header-actions">
				<?php if ( ! empty( $overall_review_url ) ) : ?>
					<a href="<?php echo esc_url( $overall_review_url ); ?>" class="doodh-btn-read-more-review" target="_blank" rel="noopener noreferrer">
						<i class="fas fa-external-link-alt"></i> <span><?php esc_html_e( 'Read More Review', 'vmtheme' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( is_user_logged_in() ) : ?>
					<button type="button" class="doodh-btn-write-review" id="doodh-toggle-review-btn" onclick="const f=document.getElementById('doodh-review-form-wrap'); if(f){ const isOpen=(f.style.display==='block'); f.style.display=isOpen?'none':'block'; if(!isOpen){ f.scrollIntoView({behavior:'smooth', block:'start'}); const input=f.querySelector('input[name=\'doodh_review_title\']'); if(input) input.focus(); } }">
						<i class="fas fa-pen-fancy"></i>
						<span><?php esc_html_e( 'Write a Review', 'vmtheme' ); ?></span>
					</button>
				<?php else : ?>
					<button type="button" class="doodh-btn-write-review doodh-auth-trigger" id="doodh-toggle-review-btn">
						<i class="fas fa-user-lock"></i>
						<span><?php esc_html_e( 'Sign In to Review', 'vmtheme' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
		</div>

		<!-- Main 2-Column Responsive Layout -->
		<div class="doodh-reviews-layout">
			<!-- Column 1: Analytics & Rating Breakdown Sidebar -->
			<aside class="doodh-reviews-sidebar">
				<div class="doodh-scorecard-card">
					<div class="doodh-scorecard-badge"><?php esc_html_e( 'Audience & Critic Score', 'vmtheme' ); ?></div>
					
					<div class="doodh-scorecard-hero">
						<div class="doodh-score-giant">
							<span class="doodh-score-num"><?php echo esc_html( number_format( $avg, 1 ) ); ?></span>
							<span class="doodh-score-scale">/10</span>
						</div>
						<div class="doodh-scorecard-stars">
							<?php
							$filled = round( $avg / 2 );
							for ( $i = 1; $i <= 5; $i++ ) {
								echo ( $i <= $filled ) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
							}
							?>
						</div>
						<div class="doodh-score-caption">
							<i class="fas fa-users"></i> <?php printf( esc_html__( 'Based on %d ratings', 'vmtheme' ), (int) $total ); ?>
						</div>
					</div>

					<!-- Recommendation Pill -->
					<div class="doodh-recommend-pill">
						<i class="fas fa-heart"></i>
						<span><strong><?php echo esc_html( $rec_pct ); ?>%</strong> <?php esc_html_e( 'of viewers recommend this title', 'vmtheme' ); ?></span>
					</div>

					<!-- Star Rating Breakdown Bars (Clickable to Filter) -->
					<div class="doodh-rating-breakdown-box">
						<div class="doodh-breakdown-title"><?php esc_html_e( 'Rating Breakdown', 'vmtheme' ); ?></div>
						<?php for ( $stars = 5; $stars >= 1; $stars-- ) : 
							$pct = $bd[ $stars ] ?? 0;
							$cnt = $star_counts[ $stars ] ?? 0;
							?>
							<button type="button" class="doodh-breakdown-row" data-filter-star="<?php echo esc_attr( $stars ); ?>" title="<?php printf( esc_attr__( 'Filter %d-Star reviews', 'vmtheme' ), $stars ); ?>">
								<span class="doodh-breakdown-label"><?php echo esc_html( $stars ); ?> <i class="fas fa-star"></i></span>
								<div class="doodh-breakdown-track">
									<div class="doodh-breakdown-fill" style="width:<?php echo esc_attr( $pct ); ?>%;"></div>
								</div>
								<span class="doodh-breakdown-pct"><?php echo esc_html( $pct ); ?>%</span>
							</button>
						<?php endfor; ?>
					</div>

					<?php if ( ! empty( $overall_review_url ) ) : ?>
						<div style="margin-top:14px;">
							<a href="<?php echo esc_url( $overall_review_url ); ?>" class="doodh-btn-read-more-review doodh-btn-full" target="_blank" rel="noopener noreferrer">
								<i class="fas fa-external-link-alt"></i> <?php esc_html_e( 'Read More Review', 'vmtheme' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<!-- Direct CTA Box -->
					<div class="doodh-write-cta-box">
						<i class="fas fa-comment-dots doodh-cta-icon"></i>
						<h4><?php esc_html_e( 'Watched this title?', 'vmtheme' ); ?></h4>
						<p><?php esc_html_e( 'Share your verdict with thousands of movie lovers.', 'vmtheme' ); ?></p>
						<?php if ( is_user_logged_in() ) : ?>
							<button type="button" class="doodh-btn-secondary doodh-btn-full" onclick="document.getElementById('doodh-toggle-review-btn').click();">
								<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Add Your Rating', 'vmtheme' ); ?>
							</button>
						<?php else : ?>
							<button type="button" class="doodh-btn-secondary doodh-btn-full doodh-auth-trigger">
								<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Sign In to Rate', 'vmtheme' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>
			</aside>

			<!-- Column 2: Filter Toolbar, Write Form & Multi-Column Review Cards Grid -->
			<div class="doodh-reviews-main">
				<!-- Interactive Review Submission Card -->
				<div class="doodh-submit-review-card" id="doodh-review-form-wrap" style="display:none;">
					<?php if ( is_user_logged_in() ) : 
						$cur_user = wp_get_current_user();
						$cur_avatar = function_exists( 'doodhtheme_get_user_avatar' ) ? doodhtheme_get_user_avatar( $cur_user->ID ) : get_avatar_url( $cur_user->ID );
						$cur_name = $cur_user->display_name ?: $cur_user->user_login;
						?>
						<div class="doodh-form-card-header">
							<div class="doodh-form-card-title">
								<i class="fas fa-pen-nib"></i>
								<div>
									<h4><?php esc_html_e( 'Write Your Review', 'vmtheme' ); ?></h4>
									<span><?php printf( esc_html__( 'Posting as %s', 'vmtheme' ), esc_html( $cur_name ) ); ?></span>
								</div>
							</div>
							<button type="button" class="doodh-form-close-btn" id="doodh-close-review-form" onclick="document.getElementById('doodh-review-form-wrap').style.display='none';" aria-label="<?php esc_attr_e( 'Close', 'vmtheme' ); ?>">&times;</button>
						</div>

						<form action="<?php echo esc_url( home_url( '/wp-comments-post.php' ) ); ?>" method="post" class="doodh-review-form" id="doodh-main-review-form">
							<!-- User Preview Banner -->
							<div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); padding:8px 14px; border-radius:8px;">
								<img src="<?php echo esc_url( $cur_avatar ); ?>" width="32" height="32" style="border-radius:50%; border:2px solid var(--dt-primary); object-fit:cover;">
								<span style="font-size:13px; color:#e2e8f0; font-weight:600;"><?php echo esc_html( $cur_name ); ?></span>
								<span class="doodh-verified-badge" style="background:rgba(16,185,129,0.15); color:#10b981; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px; margin-left:auto;"><i class="fas fa-check-circle"></i> <?php esc_html_e( 'Community Member', 'vmtheme' ); ?></span>
							</div>

							<!-- Star Picker with Live Score Tooltip -->
							<div class="doodh-star-picker-module">
								<div class="doodh-star-picker-top">
									<label><strong><?php esc_html_e( 'Select Your Rating:', 'vmtheme' ); ?></strong></label>
									<span class="doodh-star-feedback-badge" id="doodh-star-feedback">
										<strong id="doodh-rating-display-num">10</strong>/10 &bull; <span id="doodh-rating-display-text"><?php esc_html_e( 'Masterpiece 🔥', 'vmtheme' ); ?></span>
									</span>
								</div>
								<div class="doodh-star-radios" id="doodh-star-selector">
									<?php 
									$rating_labels = array(
										10 => __( 'Masterpiece 🔥', 'vmtheme' ),
										9  => __( 'Outstanding 🌟', 'vmtheme' ),
										8  => __( 'Very Good ✨', 'vmtheme' ),
										7  => __( 'Good 👍', 'vmtheme' ),
										6  => __( 'Decent 🙂', 'vmtheme' ),
										5  => __( 'Average 😐', 'vmtheme' ),
										4  => __( 'Below Average 👎', 'vmtheme' ),
										3  => __( 'Poor 😕', 'vmtheme' ),
										2  => __( 'Terrible 🤢', 'vmtheme' ),
										1  => __( 'Unwatchable 💀', 'vmtheme' ),
									);
									for ( $s = 10; $s >= 1; $s-- ) : ?>
										<input type="radio" id="star-<?php echo esc_attr( $s ); ?>" name="doodh_rating" value="<?php echo esc_attr( $s ); ?>" data-label="<?php echo esc_attr( $rating_labels[ $s ] ); ?>" <?php checked( $s, 10 ); ?>>
										<label for="star-<?php echo esc_attr( $s ); ?>" title="<?php echo esc_attr( $s . '/10 - ' . $rating_labels[ $s ] ); ?>"><i class="fas fa-star"></i></label>
									<?php endfor; ?>
								</div>
							</div>

							<div class="doodh-form-group">
								<label class="doodh-form-label"><i class="fas fa-heading"></i> <?php esc_html_e( 'Headline / Title', 'vmtheme' ); ?></label>
								<input type="text" name="doodh_review_title" class="doodh-input" placeholder="<?php esc_attr_e( 'e.g. An absolute cinematic masterpiece with breathtaking visuals!', 'vmtheme' ); ?>" required>
							</div>

							<div class="doodh-form-group">
								<label class="doodh-form-label"><i class="fas fa-quote-left"></i> <?php esc_html_e( 'Your Detailed Review', 'vmtheme' ); ?></label>
								<textarea name="comment" rows="4" class="doodh-textarea" placeholder="<?php esc_attr_e( 'What did you like or dislike? How was the plot pacing, music, and direction?...', 'vmtheme' ); ?>" required></textarea>
							</div>

							<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( $post_id ); ?>">
							<input type="hidden" name="comment_parent" value="0">
							<div class="doodh-form-actions">
								<button type="submit" class="doodh-btn-primary">
									<i class="fas fa-paper-plane"></i> <?php esc_html_e( 'Publish Review', 'vmtheme' ); ?>
								</button>
								<button type="button" class="doodh-btn-secondary" id="doodh-cancel-review-btn" onclick="document.getElementById('doodh-review-form-wrap').style.display='none';">
									<i class="fas fa-times"></i> <?php esc_html_e( 'Cancel', 'vmtheme' ); ?>
								</button>
							</div>
						</form>
					<?php else : ?>
						<!-- Member Lock Prompt Card -->
						<div class="doodh-member-lock-wrap" style="text-align:center; padding:30px 20px;">
							<div style="width:54px; height:54px; border-radius:50%; background:rgba(229,9,20,0.15); border:1px solid rgba(229,9,20,0.3); color:var(--dt-primary); display:inline-flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:14px;">
								<i class="fas fa-user-lock"></i>
							</div>
							<h4 style="font-size:18px; color:#fff; margin:0 0 6px; font-weight:800;"><?php esc_html_e( 'Member Sign In Required', 'vmtheme' ); ?></h4>
							<p style="color:#94a3b8; font-size:13.5px; max-width:440px; margin:0 auto 18px; line-height:1.5;"><?php esc_html_e( 'Join our community of movie lovers to post your star rating, share detailed reviews, and save titles to your cloud watchlist.', 'vmtheme' ); ?></p>
							<div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap;">
								<button type="button" class="doodh-btn-primary doodh-auth-trigger">
									<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Sign In / Create Account', 'vmtheme' ); ?>
								</button>
								<button type="button" class="doodh-btn-secondary" onclick="document.getElementById('doodh-review-form-wrap').style.display='none';">
									<?php esc_html_e( 'Close', 'vmtheme' ); ?>
								</button>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<!-- Filter & Sorting Controls Toolbar -->
				<div class="doodh-reviews-toolbar">
					<div class="doodh-filter-pills" id="doodh-review-filters">
						<button type="button" class="doodh-filter-pill active" data-filter="all">
							<?php esc_html_e( 'All', 'vmtheme' ); ?> 
							<span class="doodh-pill-count"><?php echo esc_html( count( $reviews ) ); ?></span>
						</button>
						<?php if ( $critic_count > 0 ) : ?>
							<button type="button" class="doodh-filter-pill" data-filter="critics">
								<i class="fas fa-certificate"></i> <?php esc_html_e( 'Critics', 'vmtheme' ); ?>
								<span class="doodh-pill-count"><?php echo esc_html( $critic_count ); ?></span>
							</button>
						<?php endif; ?>
						<?php if ( $community_count > 0 ) : ?>
							<button type="button" class="doodh-filter-pill" data-filter="community">
								<i class="fas fa-users"></i> <?php esc_html_e( 'Audience', 'vmtheme' ); ?>
								<span class="doodh-pill-count"><?php echo esc_html( $community_count ); ?></span>
							</button>
						<?php endif; ?>
						<?php if ( ! empty( $star_counts[5] ) ) : ?>
							<button type="button" class="doodh-filter-pill" data-filter="star-5">
								<i class="fas fa-star" style="color:var(--dt-accent-yellow);"></i> 5★
								<span class="doodh-pill-count"><?php echo esc_html( $star_counts[5] ); ?></span>
							</button>
						<?php endif; ?>
						<?php if ( ! empty( $star_counts[4] ) ) : ?>
							<button type="button" class="doodh-filter-pill" data-filter="star-4">
								<i class="fas fa-star" style="color:var(--dt-accent-yellow);"></i> 4★
								<span class="doodh-pill-count"><?php echo esc_html( $star_counts[4] ); ?></span>
							</button>
						<?php endif; ?>
					</div>

					<div class="doodh-sort-wrapper">
						<label for="doodh-review-sort"><i class="fas fa-sort-amount-down"></i></label>
						<select id="doodh-review-sort" class="doodh-sort-select">
							<option value="highest"><?php esc_html_e( 'Highest Rating', 'vmtheme' ); ?></option>
							<option value="newest"><?php esc_html_e( 'Newest First', 'vmtheme' ); ?></option>
							<option value="helpful"><?php esc_html_e( 'Most Helpful', 'vmtheme' ); ?></option>
						</select>
					</div>
				</div>

				<!-- Reviews Column Grid -->
				<div class="doodh-reviews-grid" id="doodh-reviews-grid-container">
					<?php if ( ! empty( $reviews ) ) : ?>
						<?php foreach ( $reviews as $idx => $rev ) : 
							$r_score      = (int) ( $rev['rating'] ?? 9 );
							$r_author     = $rev['author'] ?? __( 'Viewer', 'vmtheme' );
							$r_title      = $rev['title'] ?? '';
							$r_content    = $rev['content'] ?? '';
							$r_date       = $rev['date'] ?? '';
							$r_avatar     = ! empty( $rev['avatar'] ) ? $rev['avatar'] : doodhtheme_get_fallback_avatar_url();
							$r_url        = $rev['reviewer_url'] ?? '';
							$r_review_url = ! empty( $rev['review_url'] ) ? $rev['review_url'] : '';
							$r_badge_type = $rev['badge_type'] ?? 'verified_critic';
							$r_is_curated = ! empty( $rev['is_curated'] ) || in_array( $r_badge_type, array( 'tmdb_critic', 'imdb_critic', 'editorial', 'verified_critic' ), true );
							$r_star_tier  = ( $r_score >= 9 ) ? '5' : ( ( $r_score >= 7 ) ? '4' : ( ( $r_score >= 5 ) ? '3' : ( ( $r_score >= 3 ) ? '2' : '1' ) ) );
							$review_id    = ! empty( $rev['comment_id'] ) ? $rev['comment_id'] : 'curated-' . $idx;
							$helpful_seed = ( ( $idx * 7 + 13 ) % 23 ) + 3; // Realistic starting seed
							$is_long      = ( mb_strlen( strip_tags( $r_content ) ) > 260 );
							?>
							<article class="doodh-review-card" 
								data-rating="<?php echo esc_attr( $r_score ); ?>" 
								data-date="<?php echo esc_attr( strtotime( $r_date ?: 'now' ) ); ?>" 
								data-helpful="<?php echo esc_attr( $helpful_seed ); ?>"
								data-type="<?php echo $r_is_curated ? 'critics' : 'community'; ?>"
								data-star="star-<?php echo esc_attr( $r_star_tier ); ?>"
								data-review-id="<?php echo esc_attr( $review_id ); ?>">
								
								<!-- Card Top: Avatar, Name, Badge & Score Pill -->
								<div class="doodh-card-top-row">
									<div class="doodh-card-reviewer-meta">
										<div class="doodh-reviewer-avatar-wrap">
											<?php if ( $r_url ) : ?>
												<a href="<?php echo esc_url( $r_url ); ?>" class="doodh-reviewer-avatar-link" title="<?php printf( esc_attr__( 'View %s profile & reviews', 'vmtheme' ), esc_attr( $r_author ) ); ?>">
													<img src="<?php echo esc_url( $r_avatar ); ?>" class="doodh-reviewer-avatar" alt="<?php echo esc_attr( $r_author ); ?>" width="44" height="44" loading="lazy" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
												</a>
											<?php else : ?>
												<img src="<?php echo esc_url( $r_avatar ); ?>" class="doodh-reviewer-avatar" alt="<?php echo esc_attr( $r_author ); ?>" width="44" height="44" loading="lazy" onerror="this.onerror=null;this.src='<?php echo esc_url( doodhtheme_get_fallback_avatar_url() ); ?>';">
											<?php endif; ?>
										</div>

										<div class="doodh-reviewer-text">
											<div class="doodh-reviewer-name-row">
												<?php if ( $r_url ) : ?>
													<a href="<?php echo esc_url( $r_url ); ?>" class="doodh-reviewer-name-link">
														<?php echo esc_html( $r_author ); ?>
													</a>
												<?php else : ?>
													<span class="doodh-reviewer-name"><?php echo esc_html( $r_author ); ?></span>
												<?php endif; ?>
											</div>
											<div class="doodh-reviewer-submeta">
												<?php echo doodhtheme_get_reviewer_badge_html( $r_badge_type ); ?>
												<span class="doodh-card-date">
													<i class="far fa-calendar-alt"></i> <?php echo esc_html( date_i18n( 'M j, Y', strtotime( $r_date ?: 'now' ) ) ); ?>
												</span>
											</div>
										</div>
									</div>

									<div class="doodh-card-score-pill" title="<?php echo esc_attr( $r_score . '/10' ); ?>">
										<i class="fas fa-star"></i>
										<span><strong><?php echo esc_html( $r_score ); ?></strong>/10</span>
									</div>
								</div>

								<!-- Card Headline -->
								<?php if ( ! empty( $r_title ) ) : ?>
									<h4 class="doodh-review-headline"><?php echo esc_html( $r_title ); ?></h4>
								<?php endif; ?>

								<!-- Card Body Text with Read More toggle -->
								<div class="doodh-review-text-wrap <?php echo $is_long ? 'is-collapsible' : ''; ?>">
									<div class="doodh-review-text-content">
										<?php echo wpautop( esc_html( $r_content ) ); ?>
									</div>
									<?php if ( $is_long ) : ?>
										<button type="button" class="doodh-read-more-toggle">
											<span><?php esc_html_e( 'Read full review', 'vmtheme' ); ?></span> <i class="fas fa-chevron-down"></i>
										</button>
									<?php endif; ?>
								</div>

								<!-- Card Footer: Helpful counter & Read More Review / Source link -->
								<div class="doodh-card-footer">
									<div class="doodh-card-footer-left">
										<button type="button" class="doodh-helpful-btn" data-review-id="<?php echo esc_attr( $review_id ); ?>" title="<?php esc_attr_e( 'Did you find this review helpful?', 'vmtheme' ); ?>">
											<i class="far fa-thumbs-up"></i>
											<span><?php esc_html_e( 'Helpful', 'vmtheme' ); ?></span>
											<span class="doodh-helpful-counter"><?php echo esc_html( $helpful_seed ); ?></span>
										</button>
									</div>
									<div class="doodh-card-footer-right">
										<?php if ( ! empty( $r_review_url ) ) : ?>
											<a href="<?php echo esc_url( $r_review_url ); ?>" class="doodh-read-more-review-link" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Read More Review', 'vmtheme' ); ?>">
												<span><?php esc_html_e( 'Read More Review', 'vmtheme' ); ?></span> <i class="fas fa-external-link-alt"></i>
											</a>
										<?php elseif ( $r_url ) : ?>
											<a href="<?php echo esc_url( $r_url ); ?>" class="doodh-critic-all-link">
												<?php esc_html_e( 'More reviews', 'vmtheme' ); ?> <i class="fas fa-arrow-right"></i>
											</a>
										<?php endif; ?>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					<?php else : ?>
						<!-- Empty State Card -->
						<div class="doodh-no-reviews-card">
							<div class="doodh-no-reviews-icon">
								<i class="fas fa-film"></i>
							</div>
							<h4><?php esc_html_e( 'No Reviews Yet', 'vmtheme' ); ?></h4>
							<p><?php esc_html_e( 'Be the first person to share an insightful review and rating with our community!', 'vmtheme' ); ?></p>
							<button type="button" class="doodh-btn-primary" onclick="document.getElementById('doodh-toggle-review-btn').click();">
								<i class="fas fa-pen-fancy"></i> <?php esc_html_e( 'Write First Review', 'vmtheme' ); ?>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Render Admin Reviews & Ratings Central Management Page
 */
function doodhtheme_render_admin_reviews_page() {
	$recent_posts = get_posts( array(
		'post_type'      => array( 'movies', 'tvshows', 'post' ),
		'posts_per_page' => 30,
		'post_status'    => 'publish',
	) );
	$reviewers = get_terms( array(
		'taxonomy'   => 'dtreviewer',
		'hide_empty' => false,
	) );
	?>
	<div class="wrap" style="max-width:1100px; margin-top:20px;">
		<div style="background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; padding:25px 30px; border-radius:12px; margin-bottom:25px; box-shadow:0 4px 20px rgba(0,0,0,0.15); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
			<div>
				<h1 style="color:#fff; margin:0 0 6px; font-size:24px; font-weight:800; display:flex; align-items:center; gap:10px;">
					<span style="background:linear-gradient(135deg,#f59e0b,#d97706); width:38px; height:38px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 2px 10px rgba(245,158,11,0.4);">
						<i class="dashicons dashicons-star-filled" style="color:#fff; font-size:22px; line-height:38px; height:38px; width:38px;"></i>
					</span>
					<?php esc_html_e( 'Reviews, Ratings & Critic Profiles Center', 'vmtheme' ); ?>
				</h1>
				<p style="color:#94a3b8; margin:0; font-size:14px;">
					<?php esc_html_e( 'Manage imported TMDb & IMDb critic reviews, visitor ratings, and dedicated Reviewer / Critic profile pages.', 'vmtheme' ); ?>
				</p>
			</div>
			<div>
				<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=dtreviewer' ) ); ?>" class="button button-primary" style="background:#f59e0b; border-color:#d97706; font-weight:700;">
					<i class="dashicons dashicons-id-alt" style="line-height:26px;"></i> <?php printf( esc_html__( 'Manage Reviewers (%d)', 'vmtheme' ), is_array( $reviewers ) ? count( $reviewers ) : 0 ); ?>
				</a>
			</div>
		</div>

		<div class="postbox" style="border-radius:10px; border:1px solid #cbd5e1; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:25px; overflow:hidden;">
			<div class="postbox-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:15px 20px;">
				<h2 style="font-size:16px; font-weight:700; margin:0;">
					<i class="dashicons dashicons-format-chat" style="color:#2563eb;"></i> <?php esc_html_e( 'Recent Titles & Reviews Breakdown', 'vmtheme' ); ?>
				</h2>
			</div>
			<div class="inside" style="padding:0;">
				<table class="wp-list-table widefat fixed striped" style="border:none;">
					<thead>
						<tr>
							<th style="width:250px;"><?php esc_html_e( 'Title', 'vmtheme' ); ?></th>
							<th style="width:100px;"><?php esc_html_e( 'Type', 'vmtheme' ); ?></th>
							<th style="width:120px;"><?php esc_html_e( 'Community Score', 'vmtheme' ); ?></th>
							<th style="width:120px;"><?php esc_html_e( 'Total Reviews', 'vmtheme' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'vmtheme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $recent_posts ) ) : ?>
							<?php foreach ( $recent_posts as $p ) : 
								$metrics = doodhtheme_get_reviews_metrics( $p->ID );
								?>
								<tr>
									<td><strong><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></a></strong></td>
									<td><span class="badge" style="background:#e2e8f0; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600; text-transform:uppercase;"><?php echo esc_html( get_post_type( $p->ID ) ); ?></span></td>
									<td><strong style="color:#d97706;"><span class="dashicons dashicons-star-filled" style="font-size:14px; width:14px; height:14px;"></span> <?php echo esc_html( $metrics['avg_rating'] ); ?>/10</strong></td>
									<td><span class="badge" style="background:#ecfdf5; color:#059669; padding:3px 8px; border-radius:4px; font-weight:700; font-size:12px;"><?php echo esc_html( count( $metrics['reviews'] ) ); ?> reviews</span></td>
									<td>
										<a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" class="button button-small button-primary"><?php esc_html_e( 'Edit / Add Reviews', 'vmtheme' ); ?></a>
										<a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>#doodh-reviews-box" class="button button-small" target="_blank"><?php esc_html_e( 'View on Site', 'vmtheme' ); ?></a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5" style="padding:20px; text-align:center;"><?php esc_html_e( 'No posts found.', 'vmtheme' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Custom Comment Callback for Modern Column-Wise Layout & Streaming Dark UI
 */
function doodhtheme_custom_comment_format( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$rating = get_comment_meta( $comment->comment_ID, '_doodh_review_rating', true );
	$title  = get_comment_meta( $comment->comment_ID, '_doodh_review_title', true );
	?>
	<li <?php comment_class( empty( $args['has_children'] ) ? 'doodh-comment-item' : 'doodh-comment-item parent' ); ?> id="comment-<?php comment_ID(); ?>">
		<article id="div-comment-<?php comment_ID(); ?>" class="doodh-comment-body">
			<div class="doodh-comment-avatar-col">
				<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'class' => 'doodh-comment-avatar' ) ); ?>
			</div>

			<div class="doodh-comment-content-col">
				<div class="doodh-comment-header-row">
					<div class="doodh-comment-author-info">
						<h5 class="doodh-comment-author-name">
							<?php echo get_comment_author_link( $comment ); ?>
							<?php if ( (int) $comment->user_id === (int) get_the_author_meta( 'ID' ) ) : ?>
								<span class="doodh-author-badge"><?php esc_html_e( 'Author', 'vmtheme' ); ?></span>
							<?php endif; ?>
						</h5>
						<span class="doodh-comment-meta-time">
							<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
								<i class="far fa-clock"></i> <?php printf( esc_html__( '%1$s at %2$s', 'vmtheme' ), get_comment_date( '', $comment ), get_comment_time() ); ?>
							</a>
						</span>
					</div>

					<?php if ( ! empty( $rating ) ) : ?>
						<div class="doodh-card-score-pill" title="<?php echo esc_attr( $rating . '/10' ); ?>">
							<i class="fas fa-star"></i>
							<span><strong><?php echo esc_html( $rating ); ?></strong>/10</span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $title ) ) : ?>
					<h5 class="doodh-comment-review-title"><?php echo esc_html( $title ); ?></h5>
				<?php endif; ?>

				<?php if ( '0' == $comment->comment_approved ) : ?>
					<div class="doodh-comment-awaiting-moderation">
						<i class="fas fa-hourglass-half"></i> <?php esc_html_e( 'Your comment is awaiting moderation.', 'vmtheme' ); ?>
					</div>
				<?php endif; ?>

				<div class="doodh-comment-text">
					<?php comment_text(); ?>
				</div>

				<div class="doodh-comment-actions">
					<?php
					comment_reply_link( array_merge( $args, array(
						'add_below' => 'div-comment',
						'depth'     => $depth,
						'max_depth' => $args['max_depth'],
						'before'    => '<span class="doodh-comment-reply-wrap">',
						'after'     => '</span>',
						'reply_text'=> '<i class="fas fa-reply"></i> ' . esc_html__( 'Reply', 'vmtheme' ),
					) ) );
					?>
				</div>
			</div>
		</article>
	<?php
}

