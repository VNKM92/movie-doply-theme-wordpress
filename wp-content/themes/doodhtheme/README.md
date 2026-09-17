# 🎬 VMTheme (DoodhTheme) — Theme Documentation & Architecture Reference

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3-777BB4.svg)](https://php.net)
[![SEO Ready](https://img.shields.io/badge/SEO-Schema.org%20JSON--LD-success.svg)](#-seo-schemaorg--structured-data-engine)
[![LLM & AI Agent Ready](https://img.shields.io/badge/AI%20Agent-llms.txt%20%2B%20REST%20API-purple.svg)](#-llm--ai-browser-agentic-architecture)

**VMTheme** is an enterprise-grade WordPress streaming and catalog theme.

---

## 📑 Quick Directory Navigation

```text
doodhtheme/
├── style.css                     # Main theme stylesheet
├── functions.php                 # Core bootstrap & hooks
├── header.php                    # Responsive header & AI agent discovery tags
├── footer.php                    # Responsive footer & legal disclaimers
├── index.php                     # Dynamic Homepage (Homepage Section Manager)
├── archive-movies.php            # Movies archive catalog
├── archive-tvshows.php           # TV Shows archive catalog
├── single-movies.php             # Single Movie page (Player, Cast, Downloads, Reviews)
├── single-tvshows.php            # TV Series hub (Seasons & Episode grid)
├── single-episodes.php           # Episode streaming player with Next/Prev navigators
├── taxonomy.php                  # Taxonomy archive (Genre, Release Year, Quality)
├── taxonomy-dtcast.php           # Actor bio & filmography
├── taxonomy-dtdirector.php       # Director bio & catalog
├── search.php                    # Search results page
├── assets/                       # CSS, JS, SVG vector placeholders
└── inc/                          # Modular PHP Feature Modules
    ├── agentic-api.php           # LLM/AI Agent REST APIs (/vmtheme/v1/)
    ├── homepage-manager.php      # Dynamic Homepage Manager & WP Admin dashboard
    ├── seo-schema.php            # Schema.org JSON-LD & BreadcrumbList
    ├── tmdb-fetcher.php          # TMDb/IMDb API sync, auto-importer & reviews sync
    ├── duplicate-validator.php   # 4-Field unique collision prevention guard
    ├── post-types.php            # CPTs: movies, tvshows, seasons, episodes
    ├── taxonomies.php            # Taxonomies: genres, release-year, quality, cast, director
    ├── meta-boxes.php            # Admin custom metaboxes
    ├── player.php                # Multi-server streaming embed switcher
    ├── downloads-manager.php     # Multi-quality download links
    ├── reviews-system.php        # 10-Star user reviews & weighted rating calculator
    ├── ajax-search.php           # Real-time AJAX live search
    ├── filter.php                # Multi-parameter AJAX & URL filter query parser
    ├── brand-settings.php        # Dynamic Brand & Logo customizer
    ├── sitemap-seo.php           # XML sitemaps with Google Image extensions
    ├── redirects-manager.php     # 301 Redirects & 404 Auto-Healing
    └── ads-manager.php           # Strategic Ad slots
```

Refer to the master [Root README.md](file:///c:/xampp/htdocs/movie/README.md) for full architectural documentation, database ERD, TMDb field mappings, and AI REST API specifications.