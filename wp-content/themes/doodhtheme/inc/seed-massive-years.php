<?php
/**
 * Massive 100 Posts Per Year Dataset Seeder (2010 to 2026)
 * Generates 100 high-quality production titles for each year with posters, backdrops, cast, crew, trailers, servers, and downloads.
 *
 * @package DoodhTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Master Year-by-Year Curated Database Dictionary
 */
function doodhtheme_get_massive_year_data( $target_year ) {
	$genres_pool = array(
		array( 'Action', 'Adventure', 'Sci-Fi' ),
		array( 'Drama', 'Thriller', 'Mystery' ),
		array( 'Crime', 'Drama', 'Action' ),
		array( 'Animation', 'Adventure', 'Comedy' ),
		array( 'Horror', 'Mystery', 'Thriller' ),
		array( 'Comedy', 'Romance', 'Drama' ),
		array( 'Sci-Fi', 'Action', 'Fantasy' ),
		array( 'Biography', 'Drama', 'History' ),
	);

	$directors_pool = array(
		'Christopher Nolan', 'Denis Villeneuve', 'Quentin Tarantino', 'Martin Scorsese',
		'David Fincher', 'James Cameron', 'Steven Spielberg', 'Guillermo del Toro',
		'Ridley Scott', 'Greta Gerwig', 'Bong Joon-ho', 'Jordan Peele',
		'Taika Waititi', 'Matt Reeves', 'Chad Stahelski', 'Damien Chazelle',
		'The Russo Brothers', 'Sam Raimi', 'Zack Snyder', 'George Miller'
	);

	$actors_pool = array(
		array( 'name' => 'Leonardo DiCaprio', 'photo' => 'https://image.tmdb.org/t/p/w185/wo2pa3nr4e9kybzi5k2x21J0hW4.jpg' ),
		array( 'name' => 'Cillian Murphy', 'photo' => 'https://image.tmdb.org/t/p/w185/360Rz7e3n2U5cIeFk5e3r2K2E1.jpg' ),
		array( 'name' => 'Christian Bale', 'photo' => 'https://image.tmdb.org/t/p/w185/b7fTC9WFkgqGOv771QIezE7pQ8Z.jpg' ),
		array( 'name' => 'Tom Hardy', 'photo' => 'https://image.tmdb.org/t/p/w185/d8MbUziD4ewh8Z4zO9p77zE0bB.jpg' ),
		array( 'name' => 'Margot Robbie', 'photo' => 'https://image.tmdb.org/t/p/w185/euDPyqLnupkFi1mRwwnmu5564.jpg' ),
		array( 'name' => 'Ryan Gosling', 'photo' => 'https://image.tmdb.org/t/p/w185/lyUyVARlaToPGaxyOzA9vH2hBG.jpg' ),
		array( 'name' => 'Zendaya', 'photo' => 'https://image.tmdb.org/t/p/w185/r2Q1Dk5G3G4bV1J6E2J3r5g2F5.jpg' ),
		array( 'name' => 'Timothée Chalamet', 'photo' => 'https://image.tmdb.org/t/p/w185/BE2sdjpgEV2rNTFaUQvy79Zq2.jpg' ),
		array( 'name' => 'Pedro Pascal', 'photo' => 'https://image.tmdb.org/t/p/w185/d4T9G2K8s8r7k8y3H3z3X2W1Q1.jpg' ),
		array( 'name' => 'Ana de Armas', 'photo' => 'https://image.tmdb.org/t/p/w185/3vxvsmYsoEOGOWKJ119vB0Qoxps.jpg' ),
		array( 'name' => 'Florence Pugh', 'photo' => 'https://image.tmdb.org/t/p/w185/eP4i7B2F6b7h8Z9v4c3b2n1m0.jpg' ),
		array( 'name' => 'Keanu Reeves', 'photo' => 'https://image.tmdb.org/t/p/w185/4D0PpNI0kmP58hgrwGC3UB1xYuu.jpg' ),
	);

	$curated_yearly_titles = array(
		2010 => array( 'Inception', 'Shutter Island', 'The Social Network', 'Toy Story 3', 'The Walking Dead', 'Black Swan', 'The King\'s Speech', 'How to Train Your Dragon', 'Tangled', 'Kick-Ass', 'Despicable Me', 'Scott Pilgrim vs. the World', 'The Town', '127 Hours', 'Tron: Legacy', 'Easy A', 'The Fighter', 'Iron Man 2', 'True Grit', 'Sherlock' ),
		2011 => array( 'Game of Thrones', 'Harry Potter Deathly Hallows 2', 'Drive', 'The Intouchables', 'Black Mirror', 'Captain America', 'Thor', 'Crazy, Stupid, Love.', 'The Girl with the Dragon Tattoo', 'Source Code', 'X-Men: First Class', 'Rise of the Planet of the Apes', 'Midnight in Paris', 'Warrior', 'Mission: Impossible - Ghost Protocol', 'Moneyball', 'The Help', 'Hugo', 'Limitless', 'Suits' ),
		2012 => array( 'The Dark Knight Rises', 'The Avengers', 'Django Unchained', 'Skyfall', 'Arrow', 'Life of Pi', 'Silver Linings Playbook', 'The Hunger Games', 'The Perks of Being a Wallflower', 'The Hobbit', '21 Jump Street', 'Looper', 'Prometheus', 'The Amazing Spider-Man', 'Chronicle', 'Moonrise Kingdom', 'Brave', 'Ted', 'Vikings', 'Homeland' ),
		2013 => array( 'Prisoners', 'The Wolf of Wall Street', 'Gravity', 'The Great Gatsby', 'Peaky Blinders', 'Rick and Morty', 'Her', 'Rush', '12 Years a Slave', 'The Conjuring', 'About Time', 'Captain Phillips', 'Dallas Buyers Club', 'Snowpiercer', 'Man of Steel', 'The Hunger Games: Catching Fire', 'Now You See Me', 'World War Z', 'Pacific Rim', 'The Blacklist' ),
		2014 => array( 'Interstellar', 'Whiplash', 'Gone Girl', 'Guardians of the Galaxy', 'John Wick', 'Fargo', 'The Grand Budapest Hotel', 'Nightcrawler', 'Birdman', 'Edge of Tomorrow', 'Captain America: The Winter Soldier', 'The Imitation Game', 'X-Men: Days of Future Past', 'Ex Machina', 'Kingsman: The Secret Service', 'True Detective', 'Gotham', 'The Flash', 'Bojack Horseman', 'Silicon Valley' ),
		2015 => array( 'Mad Max: Fury Road', 'The Revenant', 'Sicario', 'The Martian', 'Better Call Saul', 'Narcos', 'Inside Out', 'Star Wars: The Force Awakens', 'Spotlight', 'The Big Short', 'Room', 'Creed', 'Bridge of Spies', 'Avengers: Age of Ultron', 'Mr. Robot', 'Daredevil', 'Sense8', 'Supergirl', 'Lucifer', 'The Man in the High Castle' ),
		2016 => array( 'Stranger Things', 'La La Land', 'Arrival', 'Deadpool', 'Captain America: Civil War', 'Westworld', 'Rogue One: A Star Wars Story', 'Hacksaw Ridge', 'Your Name', 'Zootopia', 'Manchester by the Sea', 'Moonlight', 'Doctor Strange', 'The Crown', 'Atlanta', 'This Is Us', 'Fleabag', 'The Good Place', 'Westworld S1', 'The OA' ),
		2017 => array( 'Blade Runner 2049', 'Dunkirk', 'Coco', 'Get Out', 'Dark', 'Ozark', 'Logan', 'Three Billboards Outside Ebbing, Missouri', 'Thor: Ragnarok', 'Spider-Man: Homecoming', 'The Shape of Water', 'Baby Driver', 'Lady Bird', 'Mindhunter', 'Money Heist', 'The Handmaid\'s Tale', 'The Marvelous Mrs. Maisel', 'Big Little Lies', 'Ozark S1', 'Dark S1' ),
		2018 => array( 'Avengers: Infinity War', 'Spider-Man: Into the Spider-Verse', 'A Quiet Place', 'Succession', 'Yellowstone', 'The Haunting of Hill House', 'Bohemian Rhapsody', 'Green Book', 'Hereditary', 'A Star Is Born', 'Black Panther', 'Mission: Impossible - Fallout', 'Deadpool 2', 'Cobra Kai', 'Barry', 'Killing Eve', 'Bodyguard', 'You', 'Titans', 'Manifest' ),
		2019 => array( 'Avengers: Endgame', 'Joker', 'Parasite', '1917', 'The Boys', 'The Mandalorian', 'Knives Out', 'Once Upon a Time in Hollywood', 'Ford v Ferrari', 'Jojo Rabbit', 'Marriage Story', 'The Irishman', 'Chernobyl', 'Euphoria', 'The Witcher', 'Sex Education', 'Demon Slayer', 'Watchmen', 'His Dark Materials', 'Doom Patrol' ),
		2020 => array( 'Tenet', 'The Queen\'s Gambit', 'Soul', 'The Invisible Man', 'Ted Lasso', 'Demon Slayer: Mugen Train', 'Another Round', 'Sound of Metal', 'Nomadland', 'Minari', 'The Father', 'Promising Young Woman', 'Palm Springs', 'Hamilton', 'Bridgerton', 'Gangs of London', 'Upload', 'Outer Banks', 'Alice in Borderland', 'Jujutsu Kaisen' ),
		2021 => array( 'Dune', 'Spider-Man: No Way Home', 'Squid Game', 'Arcane', 'Zack Snyder\'s Justice League', 'Loki', 'CODA', 'The Power of the Dog', 'Belfast', 'Drive My Car', 'Licorice Pizza', 'No Time to Die', 'WandaVision', 'Invincible', 'Yellowjackets', 'Maid', 'Only Murders in the Building', 'Foundation', 'Chucky', 'Shadow and Bone' ),
		2022 => array( 'Top Gun: Maverick', 'Avatar: The Way of Water', 'The Batman', 'House of the Dragon', 'Severance', 'The Bear', 'Wednesday', 'Everything Everywhere All at Once', 'The Banshees of Inisherin', 'Tár', 'Guillermo del Toro\'s Pinocchio', 'Puss in Boots: The Last Wish', 'Andor', 'Peacemaker', 'The White Lotus S2', 'Reacher', 'Heartstopper', 'Cyberpunk: Edgerunners', 'The Sandman', 'House of Dragon' ),
		2023 => array( 'Oppenheimer', 'Barbie', 'Spider-Man: Across the Spider-Verse', 'The Last of Us', 'Killers of the Flower Moon', 'Poor Things', 'Past Lives', 'Anatomy of a Fall', 'The Zone of Interest', 'The Holdovers', 'Guardians of the Galaxy Vol. 3', 'John Wick: Chapter 4', 'Godzilla Minus One', 'Beef', 'The Bear S2', 'Succession S4', 'Ahsoka', 'Gen V', 'Fallout Prep', 'One Piece Live Action' ),
		2024 => array( 'Dune: Part Two', 'Deadpool & Wolverine', 'Fallout', 'Shōgun', 'Inside Out 2', 'Gladiator II', 'Furiosa: A Mad Max Saga', 'Alien: Romulus', 'Twisters', 'Civil War', 'Challengers', 'Kingdom of the Planet of the Apes', 'The Penguin', 'House of the Dragon S2', 'The Boys S4', 'Slow Horses S4', 'True Detective: Night Country', 'Baby Reindeer', '3 Body Problem', 'Arcane S2' ),
		2025 => array( 'Superman', 'Avatar: Fire and Ash', 'Mission: Impossible - The Final Reckoning', 'Fantastic Four: First Steps', 'Stranger Things S5', 'The Last of Us S2', 'Severance S2', 'Wednesday S2', 'The White Lotus S3', 'Jurassic World: Rebirth', 'Tron: Ares', 'Blade', 'Daredevil: Born Again', 'Peacemaker S2', 'Squid Game S2', 'The Batman: Arkham', 'Mickey 17', 'Ballerina', 'Thunderbolts*', 'Captain America: Brave New World' ),
		2026 => array( 'The Batman: Part II', 'Avengers: Doomsday', 'Spider-Man 4', 'The Mandalorian & Grogu', 'Squid Game S3', 'Shrek 5', 'Star Wars: New Jedi Order', 'Toy Story 5', 'Supergirl: Woman of Tomorrow', 'Dune: Messiah Prelude', 'Fast XI', 'Horizon Zero Dawn', 'Mass Effect Series', 'God of War Series', 'Avengers: Secret Wars Prologue', 'Pirates of Caribbean Reboot', 'Harry Potter HBO S1', 'The Matrix 5', 'Inception 2 Origins', 'Interstellar Beyond' ),
	);

	$titles_list = $curated_yearly_titles[ $target_year ] ?? array();
	$items = array();

	// Generate full 100 titles for the target year
	for ( $i = 1; $i <= 100; $i++ ) {
		if ( isset( $titles_list[ $i - 1 ] ) ) {
			$title = $titles_list[ $i - 1 ];
		} else {
			$adj = array( 'The Secret', 'Beyond', 'Shadow of', 'Rise of', 'Edge of', 'Echoes of', 'Chronicles of', 'Legacy of', 'Reckoning of', 'Dawn of' )[ ( $i % 10 ) ];
			$noun = array( 'Tomorrow', 'Destiny', 'the Galaxy', 'the Void', 'the Crown', 'Eternity', 'Empire', 'the Frontier', 'Horizon', 'Infinity' )[ ( ( $i * 3 ) % 10 ) ];
			$title = "{$adj} {$noun} ({$target_year}) - Vol. {$i}";
		}

		$is_tv = ( $i % 3 === 0 ); // 1 out of 3 is a TV series
		$type  = $is_tv ? 'tvshows' : 'movies';

		$g_set = $genres_pool[ $i % count( $genres_pool ) ];
		$dir   = $directors_pool[ $i % count( $directors_pool ) ];
		$cast1 = $actors_pool[ $i % count( $actors_pool ) ];
		$cast2 = $actors_pool[ ( $i + 1 ) % count( $actors_pool ) ];
		$cast3 = $actors_pool[ ( $i + 2 ) % count( $actors_pool ) ];

		$score = number_format( 7.0 + ( ( ( $i * 17 ) % 28 ) / 10 ), 1 );
		$votes = 45000 + ( ( $i * 31250 ) % 950000 );
		$runtime = $is_tv ? ( 45 + ( ( $i * 5 ) % 25 ) ) : ( 95 + ( ( $i * 7 ) % 65 ) );

		// Authentic TMDB Image CDN sample paths
		$poster_ids = array(
			'ljsZTbVsrQSqZgWeep2B1QiDKuh.jpg', '4m1Au3YkjqsxF8iwQy0fPYSxE0V.jpg', 'kyeqWdyUXW608qlYkRqosgbbJyK.jpg',
			'wKiOkZTN9lVGWiUTviNmiazQapC.jpg', '7WsyChvgrmaJuTditCZ37As5Gds.jpg', '1E5baAaEse26fej7uHcjOgEE2t2.jpg',
			'velWPhVMQeQKcxggNEU8YmIo52R.jpg', 'gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 'd5NXSklXo0qyIYkgV94XAgMIckC.jpg',
			'udDclJoHjfjb8Ekgsd4FDteOkCU.jpg', '8ZTVqvKDQ8emSGUEMjsS4yHAwrp.jpg', 'hZkGoQYus5vegHoetLkCJzb17zJ.jpg'
		);
		$poster_img = 'https://image.tmdb.org/t/p/w500/' . $poster_ids[ $i % count( $poster_ids ) ];
		$backdrop_img = 'https://image.tmdb.org/t/p/original/' . $poster_ids[ ( $i + 2 ) % count( $poster_ids ) ];

		$items[] = array(
			'title'     => $title,
			'type'      => $type,
			'year'      => (string) $target_year,
			'tagline'   => "Experience the epic journey of {$title} in stunning 4K resolution.",
			'desc'      => "Watch {$title} ({$target_year}) streaming full HD online. Stream and download {$title} with multiple high-speed servers, original audio, and verified subtitles.",
			'runtime'   => $runtime,
			'rating'    => $score,
			'votes'     => $votes,
			'cert'      => ( $i % 2 === 0 ) ? 'PG-13' : 'R',
			'quality'   => '4K UHD',
			'genres'    => $g_set,
			'director'  => $dir,
			'cast_list' => array( $cast1, $cast2, $cast3 ),
			'poster'    => $poster_img,
			'backdrop'  => $backdrop_img,
			'trailer'   => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
		);
	}

	return $items;
}

/**
 * Seed 100 Titles for a Specific Year
 */
function doodhtheme_seed_year_batch( $year ) {
	$items = doodhtheme_get_massive_year_data( (int) $year );
	$count = 0;

	foreach ( $items as $item ) {
		// Check if already exists
		$existing = get_page_by_title( $item['title'], OBJECT, $item['type'] );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_title'   => $item['title'],
			'post_content' => $item['desc'],
			'post_status'  => 'publish',
			'post_type'    => $item['type'],
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$count++;

			// Metadata
			update_post_meta( $post_id, '_doodh_tagline', $item['tagline'] );
			update_post_meta( $post_id, '_doodh_original_title', $item['title'] );
			update_post_meta( $post_id, '_doodh_rating', $item['rating'] );
			update_post_meta( $post_id, '_doodh_votes', $item['votes'] );
			update_post_meta( $post_id, '_doodh_poster_url', $item['poster'] );
			update_post_meta( $post_id, '_doodh_backdrop_url', $item['backdrop'] );
			update_post_meta( $post_id, '_doodh_trailer_url', $item['trailer'] );
			update_post_meta( $post_id, '_doodh_certification', $item['cert'] );

			// 4 Server Tabs
			$embed_url = doodhtheme_format_youtube_embed( $item['trailer'] );
			$servers = array(
				array( 'name' => 'Server 1 - VIP 4K Stream', 'type' => 'iframe', 'url' => $embed_url ),
				array( 'name' => 'Server 2 - StreamTape HD', 'type' => 'iframe', 'url' => $embed_url ),
				array( 'name' => 'Server 3 - FastCloud 1080p', 'type' => 'iframe', 'url' => $embed_url ),
				array( 'name' => 'Server 4 - Direct Stream', 'type' => 'mp4', 'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4' ),
			);
			update_post_meta( $post_id, '_doodh_servers', $servers );

			// 3 Download Sources
			$downloads = array(
				array( 'server' => 'Mega UltraHD', 'quality' => '4K UltraHD', 'size' => '5.4 GB', 'url' => 'https://mega.nz/' ),
				array( 'server' => 'Google Drive', 'quality' => '1080p FHD', 'size' => '2.0 GB', 'url' => 'https://drive.google.com/' ),
				array( 'server' => 'Direct HD', 'quality' => '720p HD', 'size' => '900 MB', 'url' => 'https://mega.nz/' ),
			);
			update_post_meta( $post_id, '_doodh_downloads', $downloads );

			// Taxonomies
			wp_set_object_terms( $post_id, $item['genres'], 'genres' );
			wp_set_object_terms( $post_id, (string) $year, 'release-year' );
			wp_set_object_terms( $post_id, $item['quality'], 'dtquality' );
			wp_set_object_terms( $post_id, $item['director'], 'dtdirector' );

			// Rich Cast
			$actor_names = array();
			$rich_cast = array();
			foreach ( $item['cast_list'] as $actor ) {
				$actor_names[] = $actor['name'];
				$rich_cast[] = array(
					'name'      => $actor['name'],
					'character' => 'Lead Role',
					'photo'     => $actor['photo'],
				);

				// Set term meta photo
				$term = term_exists( $actor['name'], 'dtcast' );
				if ( ! $term ) {
					$term = wp_insert_term( $actor['name'], 'dtcast' );
				}
				if ( ! is_wp_error( $term ) ) {
					$term_id = is_array( $term ) ? $term['term_id'] : $term;
					update_term_meta( $term_id, '_dt_actor_photo', $actor['photo'] );
				}
			}
			wp_set_object_terms( $post_id, $actor_names, 'dtcast' );
			update_post_meta( $post_id, '_doodh_rich_cast', $rich_cast );

			// Specific Type Meta
			if ( $item['type'] === 'movies' ) {
				update_post_meta( $post_id, '_doodh_runtime', $item['runtime'] );
				update_post_meta( $post_id, '_doodh_release_date', "{$year}-06-15" );
				update_post_meta( $post_id, '_doodh_status', 'Released' );
			} else {
				update_post_meta( $post_id, '_doodh_episode_runtime', $item['runtime'] );
				update_post_meta( $post_id, '_doodh_first_air_date', "{$year}-03-01" );
				update_post_meta( $post_id, '_doodh_total_seasons', 2 );
				update_post_meta( $post_id, '_doodh_total_episodes', 16 );
				update_post_meta( $post_id, '_doodh_status', 'Returning Series' );

				// Seed Season 1
				$season_id = wp_insert_post( array(
					'post_title'   => $item['title'] . ' - Season 1',
					'post_content' => 'Season 1 of ' . $item['title'],
					'post_status'  => 'publish',
					'post_type'    => 'seasons',
				) );

				if ( $season_id && ! is_wp_error( $season_id ) ) {
					update_post_meta( $season_id, '_doodh_tv_id', $post_id );
					update_post_meta( $season_id, '_doodh_season_number', 1 );

					for ( $ep = 1; $ep <= 2; $ep++ ) {
						$ep_id = wp_insert_post( array(
							'post_title'   => sprintf( '%s S01E%02d - Episode %d', $item['title'], $ep, $ep ),
							'post_content' => sprintf( 'Stream %s Season 1 Episode %d in 4K.', $item['title'], $ep ),
							'post_status'  => 'publish',
							'post_type'    => 'episodes',
						) );

						if ( $ep_id && ! is_wp_error( $ep_id ) ) {
							update_post_meta( $ep_id, '_doodh_tv_id', $post_id );
							update_post_meta( $ep_id, '_doodh_season_number', 1 );
							update_post_meta( $ep_id, '_doodh_episode_number', $ep );
							update_post_meta( $ep_id, '_doodh_episode_name', 'Episode ' . $ep );
							update_post_meta( $ep_id, '_doodh_still_url', $item['backdrop'] );
							update_post_meta( $ep_id, '_doodh_servers', $servers );
							update_post_meta( $ep_id, '_doodh_downloads', $downloads );
						}
					}
				}
			}
		}
	}

	return $count;
}
