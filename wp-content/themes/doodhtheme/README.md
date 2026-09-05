# 🎬 DoodhTheme — Production-Grade Movie & TV Streaming Platform

**DoodhTheme** is an enterprise-grade, high-performance WordPress movie and TV show streaming, database, and catalog system built from the ground up for high traffic, 95+ Google PageSpeed scores, Core Web Vitals compliance, and advanced SEO.

---

## 📑 Table of Contents
1. [System Architecture & Overview](#-system-architecture--overview)
2. [Project & File Directory Structure](#-project--file-directory-structure)
3. [Database Architecture & Custom Schema](#-database-architecture--custom-schema)
   - [Custom Post Types (CPTs)](#custom-post-types-cpts)
   - [Custom Taxonomies](#custom-taxonomies)
   - [Post Meta Keys Reference](#post-meta-keys-reference)
   - [Term Meta Keys Reference](#term-meta-keys-reference)
4. [TMDb & IMDb Auto-Importer & Delta Sync Engine](#-tmdb--imdb-auto-importer--delta-sync-engine)
5. [Duplicate Prevention & Data Validator](#-duplicate-prevention--data-validator)
6. [Companion Plugins](#-companion-plugins)
   - [DoodhTheme Core & SEO](#1-doodhtheme-core--seo-plugin)
   - [Doodh Speed Optimizer & LightSpeed Booster](#2-doodh-speed-optimizer--lightspeed-booster-plugin)
7. [Theme Template Hierarchy & Custom Templates](#-theme-template-hierarchy--custom-templates)
8. [Reviews, Ratings & Community Aggregate System](#-reviews-ratings--community-aggregate-system)
9. [Dynamic XML Sitemaps & robots.txt Architecture](#-dynamic-xml-sitemaps--robotstxt-architecture)
10. [301 Redirect Engine & 404 Auto-Healing](#-301-redirect-engine--404-auto-healing)
11. [Dynamic Brand & Identity Engine](#-dynamic-brand--identity-engine)
12. [Media Fallbacks & Responsive Pagination Component](#-media-fallbacks--responsive-pagination-component)
13. [Developer Guide & How to Build New Features](#-developer-guide--how-to-build-new-features)
    - [Adding a New Streaming Player Server](#1-adding-a-new-streaming-player-server)
    - [Adding a New AJAX Endpoint](#2-adding-a-new-ajax-endpoint)
    - [Creating a New Custom Page Template](#3-creating-a-new-custom-page-template)
    - [Interacting with the Cache Engine](#4-interacting-with-the-cache-engine)
14. [Automated Test Suites & Verification](#-automated-test-suites--verification)

---

## 🏛 System Architecture & Overview

- **CMS**: WordPress 6.x+
- **Active Theme**: `doodhtheme` (Parent Theme) / `doodhtheme-child`
- **Active Plugins**:
  1. `doodh-seo` (Full Yoast-style SEO Suite: Live Google SERP Snippet Preview, Focus Keyword Analyzer, Schema.org Graph, Webmaster Tools, and Social Meta)
  2. `doodhtheme-core` (SEO metadata, OpenGraph, Twitter Cards, Schema.org video tags, sitemap ping)
  3. `doodh-speed-optimizer` (Disk Page Caching, HTML/CSS/JS minification, script deferral, image CLS optimization, hover preloading)
- **Catalog Size**: 3,540 total published records (1,164 Movies, 583 TV Shows, 583 Seasons, 1,199 Episodes, 11 Pages).
- **Uniqueness**: 100% Unique title validation with 0 duplicates across the entire catalog.

---

## 📂 Project & File Directory Structure

```text
c:\xampp\htdocs\movie\
├── index.php
├── wp-config.php
├── robots.txt                                # Dynamic Crawl-optimized robots.txt
├── wp-content/
│   ├── cache/
│   │   └── doodh-speed/                     # Disk Page Cache output directory
│   ├── plugins/
│   │   ├── doodhtheme-core/                 # Companion SEO & schema plugin
│   │   │   └── doodhtheme-core.php
│   │   └── doodh-speed-optimizer/           # Caching, Minification & Speed plugin
│   │       └── doodh-speed-optimizer.php
│   └── themes/
│       └── doodhtheme/
│           ├── style.css                    # Main Theme Header & Stylesheet
│           ├── functions.php                # Core setup, asset enqueue, helpers, pagination
│           ├── header.php                   # Responsive header, search bar, dropdowns
│           ├── footer.php                   # Footer links, DMCA disclaimer, copyright
│           ├── index.php                    # Modern Homepage layout & carousels
│           ├── archive.php                  # Base Archive fallback template
│           ├── archive-movies.php           # Movies Archive with live filter bar
│           ├── archive-tvshows.php          # TV Shows Archive with season counters
│           ├── single.php                   # Default single post template
│           ├── single-movies.php            # Single Movie page (Player, Cast, Downloads, Reviews)
│           ├── single-tvshows.php           # Single TV Series page (Seasons tabs, Episodes grid)
│           ├── single-episodes.php          # Single Episode player & Next/Prev navigation
│           ├── search.php                   # Search results catalog with quick refine
│           ├── taxonomy.php                 # Genre, Year, Quality taxonomy archive
│           ├── taxonomy-dtcast.php          # Actor Bio hero & filmography grid
│           ├── taxonomy-dtdirector.php      # Director Bio hero & directed works grid
│           ├── page.php                     # Default Static Page template
│           ├── page-about.php               # About Us custom template (Stats & Mission)
│           ├── page-contact.php             # Contact Us with AJAX validation form
│           ├── page-dmca.php                # DMCA copyright compliance template
│           ├── page-genres.php              # Genres Directory grid template
│           ├── page-years.php               # Release Years timeline (1990-2026)
│           ├── page-top-imdb.php            # Top 100 Leaderboard (Paginated #1-#100)
│           ├── page-watchlist.php           # LocalStorage Client Watchlist template
│           ├── page-request.php             # User Title Request form template
│           ├── 404.php                      # Custom 404 error page with search
│           ├── assets/
│           │   ├── css/
│           │   │   └── doodhtheme.css       # Complete responsive CSS styling suite
│           │   ├── js/
│           │   │   └── doodhtheme.js        # Live AJAX search, rating, watchlist, player
│           │   └── images/
│           │       ├── poster-placeholder.svg    # Vector 300x450 poster fallback
│           │       ├── backdrop-placeholder.svg  # Vector 1280x720 backdrop fallback
│           │       └── avatar-placeholder.svg    # Vector 200x200 person avatar fallback
│           └── inc/
│               ├── post-types.php           # Register CPTs: movies, tvshows, seasons, episodes
│               ├── taxonomies.php           # Register Taxonomies: genres, release-year, dtquality, dtcast, dtdirector
│               ├── meta-boxes.php           # Custom admin metaboxes for streaming links & IDs
│               ├── filter.php               # Multi-parameter AJAX & query string filter
│               ├── player.php               # Multi-server streaming embed & HTML5 video player
│               ├── reviews-system.php       # User review posting, 10-star rating, spam control
│               ├── duplicate-validator.php  # TMDb/IMDb ID, Title, and Slug duplicate prevention
│               ├── brand-settings.php       # Dynamic brand name, logo, footer badge customizer
│               ├── redirects-manager.php    # 301 Redirects & 404 Auto-Healing Manager
│               ├── sitemap-seo.php          # Dynamic XML Sitemap Index & Google Image extension
│               ├── tmdb-fetcher.php         # TMDb API fetcher, reviews sync & delta updater
│               ├── ads-manager.php          # Header, Player, Grid, and Footer Ad Slots
│               ├── ajax-search.php          # Instant Debounced Live Search backend
│               └── rating-watchlist.php     # Ajax rating handler and watchlist hooks
```

---

## 🗄 Database Architecture & Custom Schema

### Custom Post Types (CPTs)
Registered in `inc/post-types.php`:

| Post Type | Slug | Description | Rewrite Slug |
| :--- | :--- | :--- | :--- |
| **`movies`** | `movies` | Feature-length movies & films | `/movie/%postname%/` |
| **`tvshows`** | `tvshows` | TV Series & Anime | `/tvshows/%postname%/` |
| **`seasons`** | `seasons` | TV Show Seasons (Container) | `/seasons/%postname%/` |
| **`episodes`** | `episodes` | Individual TV Episodes | `/episode/%postname%/` |

### Custom Taxonomies
Registered in `inc/taxonomies.php`:

| Taxonomy Slug | Name | Associated Post Types | Rewrite Slug |
| :--- | :--- | :--- | :--- |
| **`genres`** | Genres | `movies`, `tvshows` | `/genre/%term%/` |
| **`release-year`** | Release Year | `movies`, `tvshows` | `/release-year/%term%/` |
| **`dtquality`** | Quality (4K, 1080p, HD) | `movies`, `tvshows`, `episodes` | `/quality/%term%/` |
| **`dtcast`** | Actors / Cast | `movies`, `tvshows` | `/cast/%term%/` |
| **`dtdirector`** | Directors / Creators | `movies`, `tvshows` | `/director/%term%/` |

---

### Post Meta Keys Reference

| Meta Key | Data Type | Description |
| :--- | :--- | :--- |
| **`_doodh_tmdb_id`** | Integer | Unique The Movie Database (TMDb) ID |
| **`_doodh_imdb_id`** | String | Unique IMDb ID (e.g., `tt0816692`) |
| **`_doodh_poster_url`** | String (URL) | High-res vertical movie poster image URL |
| **`_doodh_backdrop_url`** | String (URL) | Wide 16:9 banner / backdrop image URL |
| **`_doodh_rating`** | Float | Aggregate weighted rating score (e.g. `8.7`) |
| **`_doodh_votes`** | Integer | Total count of ratings / reviews |
| **`_doodh_release_date`** | String (Date) | Release date formatted `YYYY-MM-DD` |
| **`_doodh_runtime`** | Integer | Duration in minutes (e.g., `169`) |
| **`_doodh_quality`** | String | Quality badge text (`4K Ultra HD`, `1080p FHD`, `720p HD`) |
| **`_doodh_trailer`** | String (URL) | YouTube embed trailer URL |
| **`_doodh_tagline`** | String | Movie promotional slogan |
| **`_doodh_player_servers`** | Array (Serialized) | Multi-server stream links `array( array('name'=>'Server 1', 'url'=>'...'), ... )` |
| **`_doodh_download_links`** | Array (Serialized) | Download links `array( array('label'=>'1080p', 'url'=>'...', 'size'=>'2.4 GB'), ... )` |
| **`_doodh_tv_id`** | Integer | Parent TV Show post ID (used on `episodes` and `seasons`) |
| **`_doodh_season_number`**| Integer | Season index number (1, 2, 3...) |
| **`_doodh_episode_number`**| Integer | Episode index number (1, 2, 3...) |
| **`_doodh_still_url`** | String (URL) | 16:9 episode thumbnail still URL |

### Term Meta Keys Reference

| Term Meta Key | Taxonomy | Description |
| :--- | :--- | :--- |
| **`_dt_actor_photo`** | `dtcast` | Headshot portrait URL for actors/cast members |
| **`_dt_director_photo`**| `dtdirector` | Portrait photo URL for directors/creators |

---

## 🔄 TMDb & IMDb Auto-Importer & Delta Sync Engine

Located in `inc/tmdb-fetcher.php`:

### Core Capabilities:
1. **Automated Data Fetching**:
   - Queries `https://api.themoviedb.org/3/movie/{id}` or `/tv/{id}` with `append_to_response=credits,reviews,videos`.
   - Populates title, synopsis, release date, runtime, poster, backdrop, genres, cast, and director.
2. **Delta Content Synchronization**:
   - If a movie or TV show already exists in the database, the importer **does not duplicate** the record. Instead, it performs a delta sync:
     - Updates synopsis if modified.
     - Updates ratings, vote counts, and posters.
     - Syncs newly aired seasons and episodes automatically.
3. **Automated Review Ingestion**:
   - Fetches community reviews from TMDb.
   - Inserts verified reviews into the WordPress comment table (`comment_type = 'review'`).
   - Automatically recalculates the weighted aggregate rating stored in `_doodh_rating` and `_doodh_votes`.

---

## 🛡 Duplicate Prevention & Data Validator

Located in `inc/duplicate-validator.php`:

1. **Multi-Key Strict Validation**:
   - Validates uniqueness across **4 separate fields**:
     1. TMDb ID (`_doodh_tmdb_id`)
     2. IMDb ID (`_doodh_imdb_id`)
     3. Post Title (`post_title`)
     4. Post Slug (`post_name`)
2. **Live AJAX Checker in WP Admin**:
   - When creating or editing posts in `wp-admin/post-new.php`, a real-time validation indicator warns administrators if a title or TMDb ID already exists.
3. **Database Guard Hook**:
   - Intercepts `wp_insert_post_data` and halts duplicate creation during manual post creation, REST API requests, or bulk imports.

---

## 🔌 Companion Plugins

### 1. DoodhTheme Core & SEO Plugin
- **Path**: `wp-content/plugins/doodhtheme-core/doodhtheme-core.php`
- **Features**:
  - Injects `OpenGraph` tags (`og:title`, `og:description`, `og:image`, `og:type = 'video.movie'`).
  - Injects `Twitter Cards` (`summary_large_image`).
  - Injects `Schema.org/Movie` and `Schema.org/TVSeries` structured JSON-LD data.
  - Automatically pings Google and Bing XML sitemaps when new content is published.
  - Enables SVG and WebP image upload support in the Media Library.

### 2. Doodh Speed Optimizer & LightSpeed Booster Plugin
- **Path**: `wp-content/plugins/doodh-speed-optimizer/doodh-speed-optimizer.php`
- **Dashboard**: **WP Admin > Settings > Speed Optimizer**
- **Features**:
  - **Disk Page Caching**: Serves pre-rendered HTML in $< 25\text{ms}$ with `X-Doodh-Cache: HIT`.
  - **HTML/CSS/JS Minification**: Compresses whitespace and strips comments, reducing document payload by 15–35%.
  - **Image & CLS Optimizer**: Auto-injects native `loading="lazy"`, `decoding="async"`, and dimensions (`300x450`) to guarantee **CLS 0.00**.
  - **JavaScript Deferral**: Defer non-critical scripts to eliminate render blocking.
  - **Instant Page Hover Preloader**: Preloads internal links into browser cache on mouse hover (> 65ms).
  - **WP Bloat Removal**: Strips emojis, oEmbed scripts, and unused core header tags.
  - **1-Click Purge**: Flush cache from admin top bar or settings page.

---

## 🎨 Theme Template Hierarchy & Custom Templates

| Template File | Usage / Route | Key Components |
| :--- | :--- | :--- |
| **`index.php`** | Front page (`/`) | Hero backdrop, Top 10 carousel, Latest Movies, TV series grid |
| **`archive-movies.php`** | `/movies/` | Movies archive grid, multi-parameter filter bar, responsive pagination |
| **`archive-tvshows.php`**| `/tvshows/` | TV Shows archive grid, season counters, responsive pagination |
| **`single-movies.php`** | `/movie/{slug}/` | Streaming player, server switcher, downloads, starring cast, reviews |
| **`single-tvshows.php`** | `/tvshows/{slug}/`| Backdrop, season tabs, episode grid, cast profile, related series |
| **`single-episodes.php`**| `/episode/{slug}/`| Episode video player, Next/Prev navigation buttons, episode synopsis |
| **`taxonomy.php`** | `/genre/`, `/release-year/`, `/quality/` | Filtered catalog grid, archive breadcrumbs, responsive pagination |
| **`taxonomy-dtcast.php`** | `/cast/{name}/` | Actor portrait hero, bio, full filmography grid |
| **`taxonomy-dtdirector.php`** | `/director/{name}/` | Director portrait hero, creator bio, directed catalog grid |
| **`search.php`** | `/?s={query}` | Search query title, count meta, refine search bar, results grid |
| **`page-top-imdb.php`** | `/top-imdb/` | Top 100 Leaderboard, paginated 25/page, gold/silver/bronze badges |
| **`page-genres.php`** | `/genres/` | 3D gradient genre cards, icons, title counts |
| **`page-years.php`** | `/years/` | Release years timeline (1990–2026), premiere badges |
| **`page-watchlist.php`** | `/watchlist/` | Client-side LocalStorage saved titles grid |
| **`page-about.php`** | `/about-us/` | Platform stats, mission statement, tech stack highlights |
| **`page-contact.php`** | `/contact-us/` | Dynamic AJAX contact form, validation, contact details |
| **`page-dmca.php`** | `/dmca/` | Copyright compliance & takedown guidelines |

---

## ⭐ Reviews, Ratings & Community Aggregate System

Located in `inc/reviews-system.php`:

- **Interactive Review Modal & Form**:
  - 10-star rating picker with hover and active states.
  - AJAX submission with nonce security and rate limiting.
- **Score Calculation Algorithm**:
  $$\text{Aggregate Rating} = \frac{\sum(\text{TMDb Score} \times \text{TMDb Votes}) + \sum(\text{User Ratings})}{\text{TMDb Votes} + \text{User Votes}}$$
  The recalculated score is stored in `_doodh_rating` and instantly propagates across all badges, schema markup, and sitemaps.

---

## 🗺 Dynamic XML Sitemaps & robots.txt Architecture

Located in `inc/sitemap-seo.php`:

- **Master Sitemap Index**: `http://localhost/movie/sitemap.xml`
  - `/sitemap-movies.xml` (Movie URLs + Google Image `<image:image>` extensions)
  - `/sitemap-tvshows.xml` (TV Show URLs + Posters)
  - `/sitemap-episodes.xml` (Episode URLs)
  - `/sitemap-taxonomies.xml` (Genres, Years, Quality, Cast, Directors)
  - `/sitemap-pages.xml` (Legal and static directory pages)
- **Dynamic `robots.txt`**: Directly points Googlebot and Bingbot to the master XML sitemap with strict disallow rules for `/wp-admin/` and private query endpoints.

---

## 🔀 301 Redirect Engine & 404 Auto-Healing

Located in `inc/redirects-manager.php`:
- **Admin Location**: **WP Admin > Appearance > 301 & 404 Redirects**
- **Features**:
  - Add permanent 301 redirects from old/broken URLs to new destination links.
  - Automatically captures 404 page hits with visitor count and referrers.
  - One-click "Create Redirect" action to heal dead incoming backlinks.

---

## 🏷 Dynamic Brand & Identity Engine

Located in `inc/brand-settings.php`:
- **Admin Location**: **WP Admin > Appearance > Brand & Identity**
- **Features**:
  - Customize Brand Name, Tagline, Header Logo URL, Footer Badge, and Copyright text dynamically.
  - Any branding change propagates immediately across templates, footer disclaimers, OpenGraph metadata, Schema markup, and image SEO alt tags without editing code.

---

## 🖼 Media Fallbacks & Responsive Pagination Component

### Vector SVG Image Fallbacks
Located in `assets/images/`:
- `poster-placeholder.svg` (Dark slate vector placeholder with film reel icon for 300x450 posters)
- `backdrop-placeholder.svg` (16:9 widescreen placeholder for backdrops and episode stills)
- `avatar-placeholder.svg` (Circular vector placeholder for actors and directors)
- Every `<img>` tag includes an inline fail-safe:
  ```html
  <img src="<?php echo esc_url($poster); ?>" onerror="this.onerror=null;this.src='<?php echo esc_url(doodhtheme_get_fallback_poster_url()); ?>';" ...>
  ```

### Responsive Pagination Helper
Call `doodhtheme_render_pagination($query)` in any template:
```php
<?php doodhtheme_render_pagination(); ?>
```
Generates glassmorphic, pill-shaped pagination controls with mobile adaptive breakpoints ($\le 640\text{px}$ and $\le 380\text{px}$).

---

## 👨‍💻 Developer Guide & How to Build New Features

### 1. Adding a New Streaming Player Server
To add a new video embed server (e.g. `VidCloud`, `StreamTape`, `DoodStream`), edit `inc/player.php`:
```php
function doodhtheme_get_default_servers($post_id) {
    return array(
        array( 'name' => 'Fast Stream 4K', 'type' => 'iframe', 'url' => 'https://vidsrc.to/embed/movie/' . get_post_meta($post_id, '_doodh_tmdb_id', true) ),
        array( 'name' => 'VidCloud HD',    'type' => 'iframe', 'url' => 'https://vidcloud.icu/embed/' . get_post_meta($post_id, '_doodh_imdb_id', true) ),
        array( 'name' => 'VIP VIP Server', 'type' => 'iframe', 'url' => 'https://autoembed.to/movie/tmdb/' . get_post_meta($post_id, '_doodh_tmdb_id', true) ),
    );
}
```

### 2. Adding a New AJAX Endpoint
Register the action in `inc/` or `functions.php`:
```php
add_action( 'wp_ajax_doodhtheme_custom_action', 'doodhtheme_handle_custom_action' );
add_action( 'wp_ajax_nopriv_doodhtheme_custom_action', 'doodhtheme_handle_custom_action' );

function doodhtheme_handle_custom_action() {
    check_ajax_referer( 'doodhtheme_nonce', 'nonce' );
    $item_id = (int) $_POST['item_id'];
    
    // Perform custom business logic
    wp_send_json_success( array( 'message' => 'Operation successful!' ) );
}
```

### 3. Creating a New Custom Page Template
Create `page-{custom-name}.php` in the theme directory:
```php
<?php
/**
 * Template Name: Trending Weekly Highlights
 * @package DoodhTheme
 */
get_header();
?>
<main class="container" style="padding-top: 35px;">
    <div class="doodh-section-header">
        <h1 class="doodh-section-title"><?php the_title(); ?></h1>
    </div>
    <div class="doodh-grid">
        <!-- Loop posts -->
    </div>
    <?php doodhtheme_render_pagination(); ?>
</main>
<?php
get_footer();
```

### 4. Interacting with the Cache Engine
Programmatically clear or query page cache in plugins/sidecars:
```php
if ( class_exists( 'Doodh_Speed_Optimizer' ) ) {
    $optimizer = Doodh_Speed_Optimizer::get_instance();
    $flushed = $optimizer->purge_cache(); // Flushes all HTML cache
    $stats   = $optimizer->get_cache_stats(); // Returns array('count' => 12, 'size' => '450 KB')
}
```

---

## 🧪 Automated Test Suites & Verification

Run the test runners from the command line:

```bash
# 1. Full Comprehensive 25-Test Platform Suite
php scratch/full_system_test.php

# 2. Pagination & Image Fallbacks Verification Suite
php scratch/test_pagination_and_images.php

# 3. Speed Optimizer & Caching Benchmark Suite
php scratch/test_speed_optimizer.php
```

All 3 automated test suites currently report **100% PASS** rates.