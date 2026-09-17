<?php
/**
 * Custom Taxonomies for DoodhTheme (Genres, Release Year, Quality, Cast, Director, Country, Network)
 *
 * @package VMTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Taxonomies
 */
function doodhtheme_register_taxonomies() {
	// 1. Genres Taxonomy (Movies & TV Shows)
	$genre_labels = array(
		'name'                       => _x( 'Genres', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Genres', 'vmtheme' ),
		'all_items'                  => __( 'All Genres', 'vmtheme' ),
		'parent_item'                => __( 'Parent Genre', 'vmtheme' ),
		'parent_item_colon'          => __( 'Parent Genre:', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Genre', 'vmtheme' ),
		'update_item'                => __( 'Update Genre', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Genre', 'vmtheme' ),
		'new_item_name'              => __( 'New Genre Name', 'vmtheme' ),
		'menu_name'                  => __( 'Genres', 'vmtheme' ),
	);
	register_taxonomy( 'genres', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => true,
		'labels'                => $genre_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'genre', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 2. Release Year Taxonomy
	$year_labels = array(
		'name'                       => _x( 'Release Years', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Release Year', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Years', 'vmtheme' ),
		'all_items'                  => __( 'All Years', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Year', 'vmtheme' ),
		'update_item'                => __( 'Update Year', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Year', 'vmtheme' ),
		'new_item_name'              => __( 'New Year Name', 'vmtheme' ),
		'menu_name'                  => __( 'Release Years', 'vmtheme' ),
	);
	register_taxonomy( 'release-year', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $year_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'release-year', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 3. Quality Taxonomy (4K, 1080p, 720p, CAM, HD, WEBRip, BluRay)
	$quality_labels = array(
		'name'                       => _x( 'Qualities', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Quality', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Qualities', 'vmtheme' ),
		'all_items'                  => __( 'All Qualities', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Quality', 'vmtheme' ),
		'update_item'                => __( 'Update Quality', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Quality', 'vmtheme' ),
		'new_item_name'              => __( 'New Quality Name', 'vmtheme' ),
		'menu_name'                  => __( 'Quality', 'vmtheme' ),
	);
	register_taxonomy( 'dtquality', array( 'movies', 'tvshows', 'episodes' ), array(
		'hierarchical'          => false,
		'labels'                => $quality_labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'quality', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 4. Cast / Actors Taxonomy
	$cast_labels = array(
		'name'                       => _x( 'Cast / Actors', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Actor', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Actors', 'vmtheme' ),
		'all_items'                  => __( 'All Actors', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Actor', 'vmtheme' ),
		'update_item'                => __( 'Update Actor', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Actor', 'vmtheme' ),
		'new_item_name'              => __( 'New Actor Name', 'vmtheme' ),
		'menu_name'                  => __( 'Cast / Actors', 'vmtheme' ),
	);
	register_taxonomy( 'dtcast', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $cast_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'cast', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 5. Directors Taxonomy
	$director_labels = array(
		'name'                       => _x( 'Directors', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Director', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Directors', 'vmtheme' ),
		'all_items'                  => __( 'All Directors', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Director', 'vmtheme' ),
		'update_item'                => __( 'Update Director', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Director', 'vmtheme' ),
		'new_item_name'              => __( 'New Director Name', 'vmtheme' ),
		'menu_name'                  => __( 'Directors', 'vmtheme' ),
	);
	register_taxonomy( 'dtdirector', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $director_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'director', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 6. Country Taxonomy
	$country_labels = array(
		'name'                       => _x( 'Countries', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Country', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Countries', 'vmtheme' ),
		'all_items'                  => __( 'All Countries', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Country', 'vmtheme' ),
		'update_item'                => __( 'Update Country', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Country', 'vmtheme' ),
		'new_item_name'              => __( 'New Country Name', 'vmtheme' ),
		'menu_name'                  => __( 'Countries', 'vmtheme' ),
	);
	register_taxonomy( 'dtcountry', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $country_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'country', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 7. Networks / Studios Taxonomy
	$network_labels = array(
		'name'                       => _x( 'Networks & Studios', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Network', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Networks', 'vmtheme' ),
		'all_items'                  => __( 'All Networks', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Network', 'vmtheme' ),
		'update_item'                => __( 'Update Network', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Network', 'vmtheme' ),
		'new_item_name'              => __( 'New Network Name', 'vmtheme' ),
		'menu_name'                  => __( 'Networks / Studios', 'vmtheme' ),
	);
	register_taxonomy( 'dtnetwork', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $network_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'network', 'with_front' => false ),
		'show_in_rest'          => true,
	) );

	// 8. Reviewers / Critics Taxonomy (Profile Pages for each review author)
	$reviewer_labels = array(
		'name'                       => _x( 'Reviewers & Critics', 'taxonomy general name', 'vmtheme' ),
		'singular_name'              => _x( 'Reviewer', 'taxonomy singular name', 'vmtheme' ),
		'search_items'               => __( 'Search Reviewers', 'vmtheme' ),
		'all_items'                  => __( 'All Reviewers', 'vmtheme' ),
		'edit_item'                  => __( 'Edit Reviewer Profile', 'vmtheme' ),
		'update_item'                => __( 'Update Reviewer', 'vmtheme' ),
		'add_new_item'               => __( 'Add New Reviewer', 'vmtheme' ),
		'new_item_name'              => __( 'New Reviewer Name', 'vmtheme' ),
		'menu_name'                  => __( 'Reviewers', 'vmtheme' ),
	);
	register_taxonomy( 'dtreviewer', array( 'movies', 'tvshows' ), array(
		'hierarchical'          => false,
		'labels'                => $reviewer_labels,
		'show_ui'               => true,
		'show_admin_column'     => false,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'reviewer', 'with_front' => false ),
		'show_in_rest'          => true,
	) );
}
add_action( 'init', 'doodhtheme_register_taxonomies' );

/**
 * Add Custom Fields to Reviewer Taxonomy (Add Screen)
 */
function doodhtheme_dtreviewer_add_form_fields() {
	?>
	<div class="form-field">
		<label for="reviewer_avatar"><?php esc_html_e( 'Reviewer Avatar / Photo URL', 'vmtheme' ); ?></label>
		<input type="url" name="reviewer_avatar" id="reviewer_avatar" value="" placeholder="https://...">
		<p class="description"><?php esc_html_e( 'Direct image URL for the reviewer avatar.', 'vmtheme' ); ?></p>
	</div>
	<div class="form-field">
		<label for="reviewer_badge"><?php esc_html_e( 'Critic Badge / Source Type', 'vmtheme' ); ?></label>
		<select name="reviewer_badge" id="reviewer_badge">
			<option value="verified_critic"><?php esc_html_e( 'Verified Critic', 'vmtheme' ); ?></option>
			<option value="tmdb_critic"><?php esc_html_e( 'TMDb Verified Critic', 'vmtheme' ); ?></option>
			<option value="imdb_critic"><?php esc_html_e( 'IMDb Top Critic', 'vmtheme' ); ?></option>
			<option value="editorial"><?php esc_html_e( 'Editorial Staff', 'vmtheme' ); ?></option>
			<option value="community"><?php esc_html_e( 'Community Member', 'vmtheme' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Badge displayed next to reviewer name.', 'vmtheme' ); ?></p>
	</div>
	<div class="form-field">
		<label for="reviewer_site"><?php esc_html_e( 'Website / Social Profile URL', 'vmtheme' ); ?></label>
		<input type="url" name="reviewer_site" id="reviewer_site" value="" placeholder="https://...">
	</div>
	<?php
}
add_action( 'dtreviewer_add_form_fields', 'doodhtheme_dtreviewer_add_form_fields' );

/**
 * Edit Custom Fields on Reviewer Taxonomy (Edit Screen)
 */
function doodhtheme_dtreviewer_edit_form_fields( $term ) {
	$avatar = get_term_meta( $term->term_id, '_dt_reviewer_avatar', true );
	$badge  = get_term_meta( $term->term_id, '_dt_reviewer_badge', true ) ?: 'verified_critic';
	$site   = get_term_meta( $term->term_id, '_dt_reviewer_site', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="reviewer_avatar"><?php esc_html_e( 'Avatar / Photo URL', 'vmtheme' ); ?></label></th>
		<td>
			<input type="url" name="reviewer_avatar" id="reviewer_avatar" value="<?php echo esc_attr( $avatar ); ?>" class="regular-text" placeholder="https://...">
			<?php if ( $avatar ) : ?>
				<div style="margin-top:8px;">
					<img src="<?php echo esc_url( $avatar ); ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0;">
				</div>
			<?php endif; ?>
			<p class="description"><?php esc_html_e( 'Direct image URL for the reviewer avatar.', 'vmtheme' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="reviewer_badge"><?php esc_html_e( 'Critic Badge / Source Type', 'vmtheme' ); ?></label></th>
		<td>
			<select name="reviewer_badge" id="reviewer_badge">
				<option value="verified_critic" <?php selected( $badge, 'verified_critic' ); ?>><?php esc_html_e( 'Verified Critic', 'vmtheme' ); ?></option>
				<option value="tmdb_critic" <?php selected( $badge, 'tmdb_critic' ); ?>><?php esc_html_e( 'TMDb Verified Critic', 'vmtheme' ); ?></option>
				<option value="imdb_critic" <?php selected( $badge, 'imdb_critic' ); ?>><?php esc_html_e( 'IMDb Top Critic', 'vmtheme' ); ?></option>
				<option value="editorial" <?php selected( $badge, 'editorial' ); ?>><?php esc_html_e( 'Editorial Staff', 'vmtheme' ); ?></option>
				<option value="community" <?php selected( $badge, 'community' ); ?>><?php esc_html_e( 'Community Member', 'vmtheme' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Badge displayed next to reviewer name.', 'vmtheme' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="reviewer_site"><?php esc_html_e( 'Website / Social Profile URL', 'vmtheme' ); ?></label></th>
		<td>
			<input type="url" name="reviewer_site" id="reviewer_site" value="<?php echo esc_attr( $site ); ?>" class="regular-text" placeholder="https://...">
		</td>
	</tr>
	<?php
}
add_action( 'dtreviewer_edit_form_fields', 'doodhtheme_dtreviewer_edit_form_fields' );

/**
 * Save Reviewer Taxonomy Custom Fields
 */
function doodhtheme_save_dtreviewer_meta( $term_id ) {
	if ( isset( $_POST['reviewer_avatar'] ) ) {
		update_term_meta( $term_id, '_dt_reviewer_avatar', esc_url_raw( $_POST['reviewer_avatar'] ) );
	}
	if ( isset( $_POST['reviewer_badge'] ) ) {
		update_term_meta( $term_id, '_dt_reviewer_badge', sanitize_text_field( $_POST['reviewer_badge'] ) );
	}
	if ( isset( $_POST['reviewer_site'] ) ) {
		update_term_meta( $term_id, '_dt_reviewer_site', esc_url_raw( $_POST['reviewer_site'] ) );
	}
}
add_action( 'created_dtreviewer', 'doodhtheme_save_dtreviewer_meta' );
add_action( 'edited_dtreviewer', 'doodhtheme_save_dtreviewer_meta' );
