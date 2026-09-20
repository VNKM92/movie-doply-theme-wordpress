/**
 * DoodhTheme - Production Streaming JavaScript
 * Server switching, Live AJAX Search, Star Rating, Watchlist, Genre Tabs, FAQ, and Request Form
 */

(function($) {
  'use strict';

  // Backwards & Forwards Compatible Data Bridge
  var VMThemeData = window.VMThemeData || window.DoodhThemeData || {};
  var DoodhThemeData = VMThemeData;
  window.VMThemeData = VMThemeData;
  window.DoodhThemeData = DoodhThemeData;

  $(document).ready(function() {

    // ══════════════════════════════════════════════════════════════
    // 0. Responsive Navbar & Mobile Navigation Drawer
    // ══════════════════════════════════════════════════════════════
    function openMobileDrawer() {
      $('#doodh-mobile-drawer').addClass('active');
      $('#doodh-drawer-overlay').addClass('active');
      $('#doodh-mobile-menu-btn').addClass('active');
      $('body').addClass('doodh-drawer-open');
    }

    function closeMobileDrawer() {
      $('#doodh-mobile-drawer').removeClass('active');
      $('#doodh-drawer-overlay').removeClass('active');
      $('#doodh-mobile-menu-btn').removeClass('active');
      $('body').removeClass('doodh-drawer-open');
    }

    // Toggle Drawer on Hamburger Button Click
    $(document).on('click', '#doodh-mobile-menu-btn, .doodh-mobile-toggle', function(e) {
      e.preventDefault();
      e.stopPropagation();
      if ($('#doodh-mobile-drawer').hasClass('active')) {
        closeMobileDrawer();
      } else {
        openMobileDrawer();
      }
    });

    // Close Drawer on Close Button or Overlay Click
    $(document).on('click', '#doodh-drawer-close, #doodh-drawer-overlay', function(e) {
      e.preventDefault();
      closeMobileDrawer();
    });

    // Close on Escape Key
    $(document).on('keydown', function(e) {
      if (e.which === 27 && $('#doodh-mobile-drawer').hasClass('active')) {
        closeMobileDrawer();
      }
    });

    // Close Drawer when a link is clicked
    $(document).on('click', '.doodh-drawer-nav a', function() {
      closeMobileDrawer();
    });

    // Dropdown Touch/Click Support for Tablets / Touchscreens
    $(document).on('click', '.doodh-has-dropdown > a', function(e) {
      if (window.innerWidth <= 1100 || ('ontouchstart' in window)) {
        var $parent = $(this).closest('.doodh-has-dropdown');
        if (!$parent.hasClass('doodh-dropdown-open')) {
          e.preventDefault();
          $('.doodh-has-dropdown').not($parent).removeClass('doodh-dropdown-open');
          $parent.addClass('doodh-dropdown-open');
        } else {
          $parent.removeClass('doodh-dropdown-open');
        }
      }
    });

    // Close open dropdowns on outside click
    $(document).on('click', function(e) {
      if (!$(e.target).closest('.doodh-has-dropdown').length) {
        $('.doodh-has-dropdown').removeClass('doodh-dropdown-open');
      }
    });

    // 1. Video Player Server Tab Switcher
    $(document).on('click', '.doodh-server-btn', function(e) {
      e.preventDefault();
      var $btn = $(this);
      if ($btn.hasClass('active')) return;

      $('.doodh-server-btn').removeClass('active');
      $btn.addClass('active');

      var url = $btn.data('server-url');
      var type = $btn.data('server-type') || 'iframe';
      var $container = $('#player-iframe-box');
      var $loader = $('#player-loader');

      $loader.show();
      $container.empty();

      setTimeout(function() {
        if (type === 'mp4') {
          var videoHtml = '<video controls autoplay class="doodh-html5-video" id="main-video-element">' +
                          '<source src="' + url + '" type="video/mp4">' +
                          'Your browser does not support HTML5 video.' +
                          '</video>';
          $container.html(videoHtml);
        } else {
          var iframeHtml = '<iframe id="main-player-frame" src="' + url + '" frameborder="0" allowfullscreen ' +
                           'scrolling="no" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
          $container.html(iframeHtml);
        }
        $loader.hide();
      }, 300);
    });

    // 2. Lights Off Toggle
    if (!$('#doodh-lights-curtain').length) {
      $('body').append('<div id="doodh-lights-curtain"></div>');
    }

    $(document).on('click', '#doodh-btn-lights, #doodh-lights-curtain', function(e) {
      e.preventDefault();
      $('body').toggleClass('lights-off-active');
      $('#doodh-lights-curtain').fadeToggle(200);
    });

    // 3. Theater Mode Toggle
    $(document).on('click', '#doodh-btn-theater', function(e) {
      e.preventDefault();
      $('body').toggleClass('theater-mode-active');
      $(this).toggleClass('active');
    });

    // ══════════════════════════════════════════════════════════════
    // 4. Advanced Live Search Engine (Spotlight, Header & Mobile)
    // ══════════════════════════════════════════════════════════════
    var searchTimer = null;
    var activeSearchScope = 'all';
    var RECENT_SEARCHES_KEY = 'doodh_recent_searches';

    // Helper: Get Recent Searches from localStorage
    function getRecentSearches() {
      try {
        var raw = localStorage.getItem(RECENT_SEARCHES_KEY);
        return raw ? JSON.parse(raw) : [];
      } catch (e) {
        return [];
      }
    }

    // Helper: Save Recent Search
    function saveRecentSearch(term) {
      if (!term || term.trim().length < 2) return;
      term = term.trim();
      try {
        var list = getRecentSearches();
        list = list.filter(function(item) {
          return item.toLowerCase() !== term.toLowerCase();
        });
        list.unshift(term);
        if (list.length > 6) list = list.slice(0, 6);
        localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(list));
      } catch (e) {}
    }

    // Helper: Clear Recent Searches
    function clearRecentSearches() {
      try {
        localStorage.removeItem(RECENT_SEARCHES_KEY);
      } catch (e) {}
    }

    // Helper: Highlight matching query text in string
    function highlightMatch(text, query) {
      if (!query || !text) return text;
      var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      var regex = new RegExp('(' + escaped + ')', 'gi');
      return text.replace(regex, '<span class="doodh-search-highlight">$1</span>');
    }

    // Render Recent Searches & Trending Dropdown
    function renderInitialDropdown($results, $input) {
      var recents = getRecentSearches();
      var html = '<div class="doodh-search-dropdown-inner">';

      if (recents.length > 0) {
        html += '<div class="doodh-search-recent-section">' +
                  '<div class="doodh-search-subhead">' +
                    '<span><i class="fas fa-history"></i> Recent Searches</span>' +
                    '<button type="button" class="doodh-btn-clear-history" id="doodh-clear-recents-btn">Clear</button>' +
                  '</div>' +
                  '<div class="doodh-recent-chips">';
        $.each(recents, function(i, term) {
          html += '<button type="button" class="doodh-recent-chip" data-query="' + term + '">' +
                    '<i class="fas fa-clock"></i> ' + term +
                  '</button>';
        });
        html += '</div></div>';
      }

      html += '<div class="doodh-search-trending-section">' +
                '<div class="doodh-search-subhead"><span><i class="fas fa-fire"></i> Popular Suggestions</span></div>' +
                '<div class="doodh-trending-quick-list">' +
                  '<button type="button" class="doodh-trending-quick-btn" data-query="Action">🎬 Action Blockbusters</button>' +
                  '<button type="button" class="doodh-trending-quick-btn" data-query="Sci-Fi">🚀 Sci-Fi & Fantasy</button>' +
                  '<button type="button" class="doodh-trending-quick-btn" data-query="Avatar">🌌 Avatar</button>' +
                  '<button type="button" class="doodh-trending-quick-btn" data-query="Avengers">🦸 Avengers</button>' +
                  '<button type="button" class="doodh-trending-quick-btn" data-query="4K">💎 4K Ultra HD</button>' +
                '</div>' +
              '</div>';

      html += '</div>';
      $results.html(html).fadeIn(150);
    }

    // Main Live Search Handler
    function handleLiveSearch($input, $results, scope) {
      var keyword = $input.val().trim();
      var currentScope = scope || activeSearchScope || 'all';
      clearTimeout(searchTimer);

      var $clearBtn = $input.closest('form').find('.doodh-search-clear-btn');
      if (keyword.length > 0) {
        $clearBtn.fadeIn(100);
      } else {
        $clearBtn.fadeOut(100);
      }

      if (keyword.length < 2) {
        renderInitialDropdown($results, $input);
        return;
      }

      searchTimer = setTimeout(function() {
        $.ajax({
          url: DoodhThemeData.ajax_url,
          type: 'GET',
          dataType: 'json',
          data: {
            action: 'doodhtheme_ajax_search',
            keyword: keyword,
            type: currentScope,
            nonce: DoodhThemeData.nonce
          },
          beforeSend: function() {
            var skeletonHtml = '<div class="doodh-search-loading-wrap">' +
                                 '<div class="doodh-search-spinner-box"><i class="fas fa-circle-notch fa-spin"></i> <span>Searching database...</span></div>' +
                                 '<div class="doodh-search-skeleton-item"><div class="sk-poster"></div><div class="sk-lines"><div class="sk-line-1"></div><div class="sk-line-2"></div></div></div>' +
                                 '<div class="doodh-search-skeleton-item"><div class="sk-poster"></div><div class="sk-lines"><div class="sk-line-1"></div><div class="sk-line-2"></div></div></div>' +
                               '</div>';
            $results.html(skeletonHtml).show();
          },
          success: function(response) {
            if (response.success && response.data.results && response.data.results.length > 0) {
              var count = response.data.results.length;
              var html = '<div class="doodh-search-results-header">' +
                           '<span class="doodh-results-count"><i class="fas fa-check-circle"></i> Found <strong>' + count + '</strong> matches for "<em>' + keyword + '</em>"</span>' +
                           '<span class="doodh-results-hint">Use ↑↓ to navigate, ↵ to open</span>' +
                         '</div>' +
                         '<div class="doodh-live-results-list">';

              $.each(response.data.results, function(i, item) {
                var fallbackPoster = DoodhThemeData.fallback_poster || '';
                var highlightedTitle = highlightMatch(item.title, keyword);
                var qualityBadge = item.quality ? '<span class="doodh-live-quality-pill">' + item.quality + '</span>' : '';
                var genreBadge = item.genres ? '<span class="doodh-live-genre-pill">' + item.genres + '</span>' : '';
                var typeBadgeClass = (item.type_slug === 'movies') ? 'type-movie' : 'type-tv';

                html += '<a href="' + item.url + '" class="doodh-live-item" data-title="' + item.title + '">' +
                          '<div class="doodh-live-poster-wrap">' +
                            '<img src="' + item.poster + '" class="doodh-live-poster" alt="' + item.title + '" onerror="this.onerror=null;this.src=\'' + fallbackPoster + '\';">' +
                            qualityBadge +
                          '</div>' +
                          '<div class="doodh-live-info">' +
                            '<div class="doodh-live-title-row">' +
                              '<div class="doodh-live-title">' + highlightedTitle + '</div>' +
                              '<span class="doodh-live-tag ' + typeBadgeClass + '">' + item.type + '</span>' +
                            '</div>' +
                            '<div class="doodh-live-meta">' +
                              '<span class="doodh-live-year"><i class="far fa-calendar-alt"></i> ' + item.year + '</span>' +
                              (item.runtime ? '<span class="doodh-live-runtime"><i class="far fa-clock"></i> ' + item.runtime + '</span>' : '') +
                              '<span class="doodh-live-rating"><i class="fas fa-star"></i> ' + item.rating + '</span>' +
                              genreBadge +
                            '</div>' +
                          '</div>' +
                          '<div class="doodh-live-arrow"><i class="fas fa-arrow-right"></i></div>' +
                        '</a>';
              });

              html += '</div>';
              html += '<a href="' + response.data.more_url + '" class="doodh-live-view-all-btn">' +
                        '<span>View all matching titles (' + count + '+)</span> <i class="fas fa-arrow-right"></i>' +
                      '</a>';

              $results.html(html).show();
            } else {
              var noResHtml = '<div class="doodh-search-empty-state">' +
                                '<div class="doodh-empty-icon-wrap"><i class="fas fa-search"></i></div>' +
                                '<div class="doodh-empty-text">No titles found for "<strong>' + keyword + '</strong>"</div>' +
                                '<p class="doodh-empty-subtext">Try checking spelling or exploring popular categories:</p>' +
                                '<div class="doodh-empty-suggestions">' +
                                  '<a href="' + DoodhThemeData.home_url + 'movies/" class="doodh-empty-pill"><i class="fas fa-film"></i> Movies</a>' +
                                  '<a href="' + DoodhThemeData.home_url + 'tvshows/" class="doodh-empty-pill"><i class="fas fa-tv"></i> TV Series</a>' +
                                  '<a href="' + DoodhThemeData.home_url + 'top-imdb/" class="doodh-empty-pill"><i class="fas fa-trophy"></i> Top 100 IMDb</a>' +
                                  '<a href="' + DoodhThemeData.home_url + 'genres/" class="doodh-empty-pill"><i class="fas fa-tags"></i> All Genres</a>' +
                                '</div>' +
                              '</div>';
              $results.html(noResHtml).show();
            }
          }
        });
      }, 250);
    }

    // Keyboard Navigation for Results
    function handleSearchKeyNavigation(e, $input, $results) {
      var $items = $results.find('.doodh-live-item');
      if (!$items.length || !$results.is(':visible')) return;

      var $current = $items.filter('.is-active-nav');
      var index = $items.index($current);

      if (e.which === 40) { // Arrow Down
        e.preventDefault();
        if (index < $items.length - 1) {
          $items.removeClass('is-active-nav');
          $items.eq(index + 1).addClass('is-active-nav').focus();
          $items.eq(index + 1)[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
          $items.removeClass('is-active-nav');
          $items.first().addClass('is-active-nav');
        }
      } else if (e.which === 38) { // Arrow Up
        e.preventDefault();
        if (index > 0) {
          $items.removeClass('is-active-nav');
          $items.eq(index - 1).addClass('is-active-nav');
          $items.eq(index - 1)[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
          $items.removeClass('is-active-nav');
          $input.focus();
        }
      } else if (e.which === 13) { // Enter
        if ($current.length) {
          e.preventDefault();
          saveRecentSearch($input.val());
          window.location.href = $current.attr('href');
        } else if ($input.val().trim().length >= 2) {
          saveRecentSearch($input.val());
        }
      } else if (e.which === 27) { // Escape
        $results.fadeOut(150);
      }
    }

    // 1. Homepage Spotlight Search Input
    $('#doodh-home-search-input').on('input', function() {
      handleLiveSearch($(this), $('#doodh-home-live-results'), activeSearchScope);
    }).on('focus', function() {
      if ($(this).val().trim().length < 2) {
        renderInitialDropdown($('#doodh-home-live-results'), $(this));
      } else {
        handleLiveSearch($(this), $('#doodh-home-live-results'), activeSearchScope);
      }
    }).on('keydown', function(e) {
      handleSearchKeyNavigation(e, $(this), $('#doodh-home-live-results'));
    });

    // 2. Header Search Input
    $('#doodh-live-search-input').on('input', function() {
      handleLiveSearch($(this), $('#doodh-live-results-box'), 'all');
    }).on('focus', function() {
      if ($(this).val().trim().length < 2) {
        renderInitialDropdown($('#doodh-live-results-box'), $(this));
      } else {
        handleLiveSearch($(this), $('#doodh-live-results-box'), 'all');
      }
    }).on('keydown', function(e) {
      handleSearchKeyNavigation(e, $(this), $('#doodh-live-results-box'));
    });

    // 3. Mobile Dropdown Search Input
    $('#doodh-mobile-search-input').on('input', function() {
      handleLiveSearch($(this), $('#doodh-mobile-live-results-box'), 'all');
    }).on('focus', function() {
      if ($(this).val().trim().length < 2) {
        renderInitialDropdown($('#doodh-mobile-live-results-box'), $(this));
      } else {
        handleLiveSearch($(this), $('#doodh-mobile-live-results-box'), 'all');
      }
    }).on('keydown', function(e) {
      handleSearchKeyNavigation(e, $(this), $('#doodh-mobile-live-results-box'));
    });

    // Save recent search on form submit
    $(document).on('submit', '#doodh-home-search-form, .doodh-search-form, .doodh-mobile-search-form', function() {
      var val = $(this).find('input[type="search"]').val();
      saveRecentSearch(val);
    });

    // Save recent search on item click
    $(document).on('click', '.doodh-live-item', function() {
      var term = $(this).data('title');
      saveRecentSearch(term);
    });

    // Clear Search Buttons Click
    $(document).on('click', '.doodh-search-clear-btn', function(e) {
      e.preventDefault();
      var $form = $(this).closest('form');
      var $input = $form.find('input[type="search"]');
      var $results = $form.siblings('.doodh-live-results, .doodh-home-live-results');
      if (!$results.length) {
        $results = $form.parent().find('.doodh-live-results, .doodh-home-live-results');
      }
      $input.val('').focus();
      $(this).fadeOut(100);
      renderInitialDropdown($results, $input);
    });

    // Scope Pill Switcher
    $('.doodh-scope-pill').on('click', function(e) {
      e.preventDefault();
      $('.doodh-scope-pill').removeClass('active');
      $(this).addClass('active');
      var scope = $(this).data('scope') || 'all';
      activeSearchScope = scope;
      $('#doodh-home-search-scope-input').val(scope === 'all' ? '' : scope);

      var $input = $('#doodh-home-search-input');
      if ($input.val().trim().length >= 2) {
        handleLiveSearch($input, $('#doodh-home-live-results'), scope);
      }
    });

    // Quick Trending Pills & Recent Chips Click
    $(document).on('click', '.doodh-quick-pill, .doodh-recent-chip, .doodh-trending-quick-btn', function(e) {
      e.preventDefault();
      var query = $(this).data('query');
      if (!query) return;

      var $homeInput = $('#doodh-home-search-input');
      if ($homeInput.length && $homeInput.is(':visible')) {
        $homeInput.val(query).focus();
        $('html, body').animate({
          scrollTop: $('#doodh-home-search-section').offset().top - 80
        }, 300);
        handleLiveSearch($homeInput, $('#doodh-home-live-results'), activeSearchScope);
      } else {
        var $headerInput = $('#doodh-live-search-input');
        $headerInput.val(query).focus();
        handleLiveSearch($headerInput, $('#doodh-live-results-box'), 'all');
      }
    });

    // Clear Recents Button Click
    $(document).on('click', '#doodh-clear-recents-btn', function(e) {
      e.preventDefault();
      clearRecentSearches();
      $('.doodh-search-recent-section').slideUp(150, function() {
        $(this).remove();
      });
    });

    // Global Keyboard Shortcut (Ctrl+K or Cmd+K or /) to focus search
    $(document).on('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k' || (e.key === '/' && !$(e.target).is('input, textarea, select'))) {
        e.preventDefault();
        var $homeInput = $('#doodh-home-search-input');
        if ($homeInput.length && $homeInput.is(':visible')) {
          $('html, body').animate({
            scrollTop: $('#doodh-home-search-section').offset().top - 80
          }, 200, function() {
            $homeInput.focus().select();
          });
        } else {
          $('#doodh-live-search-input').focus().select();
        }
      }
    });

    // Dynamic Rotating Placeholder Effect for Home Search
    var dynamicPlaceholders = [
      'Search 10,000+ Movies, TV Series, Actors & Directors...',
      'Try searching "Oppenheimer", "Dune", "Avatar"...',
      'Looking for series? Try "Stranger Things", "Loki"...',
      'Search by director, e.g. "Christopher Nolan"...',
      'Search by genre, e.g. "Action", "Sci-Fi", "Anime"...'
    ];
    var currentPlaceholderIdx = 0;
    var $homeSearchInputEl = $('#doodh-home-search-input');

    if ($homeSearchInputEl.length) {
      setInterval(function() {
        if (!$homeSearchInputEl.is(':focus') && !$homeSearchInputEl.val().trim()) {
          currentPlaceholderIdx = (currentPlaceholderIdx + 1) % dynamicPlaceholders.length;
          $homeSearchInputEl.attr('placeholder', dynamicPlaceholders[currentPlaceholderIdx]);
        }
      }, 3500);
    }

    // Mobile Search Bar Toggle
    $('#doodh-mobile-search-btn').on('click', function(e) {
      e.preventDefault();
      var $bar = $('#doodh-mobile-search-bar');
      $bar.slideToggle(200, function() {
        if ($bar.is(':visible')) {
          $('#doodh-mobile-search-input').focus();
        }
      });
    });

    // Close Results on Outside Click
    $(document).on('click', function(e) {
      if (!$(e.target).closest('#doodh-home-search-section, .doodh-search-box, .doodh-mobile-search-bar').length) {
        $('#doodh-home-live-results, #doodh-live-results-box, #doodh-mobile-live-results-box').fadeOut(150);
      }
    });

    // 5. Interactive Star Rating
    $('.doodh-star').on('mouseenter', function() {
      var val = $(this).data('val');
      var $stars = $(this).closest('.doodh-stars').find('.doodh-star');
      $stars.each(function() {
        if ($(this).data('val') <= val) {
          $(this).addClass('hover');
        } else {
          $(this).removeClass('hover');
        }
      });
    });

    $('.doodh-stars').on('mouseleave', function() {
      $(this).find('.doodh-star').removeClass('hover');
    });

    $('.doodh-star').on('click', function() {
      var $parent = $(this).closest('.doodh-rating-box');
      var postId = $parent.data('post-id');
      var rating = $(this).data('val');
      var $feedback = $('#doodh-rate-feedback');

      if ($parent.find('.doodh-stars').data('voted') === true) {
        $feedback.text('You have already voted.');
        return;
      }

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'doodhtheme_rate_post',
          post_id: postId,
          rating: rating,
          nonce: DoodhThemeData.nonce
        },
        success: function(res) {
          if (res.success) {
            $('#doodh-current-score').text(res.data.rating);
            $('#doodh-current-votes').text(res.data.votes);
            $feedback.text(res.data.message);
            $parent.find('.doodh-stars').data('voted', true);
          } else {
            $feedback.text(res.data.message);
          }
        }
      });
    });

    // 6. Watchlist LocalStorage & Cloud Sync System
    function getWatchlist() {
      try {
        return JSON.parse(localStorage.getItem('doodh_watchlist')) || [];
      } catch (e) {
        return [];
      }
    }

    function saveWatchlist(items) {
      localStorage.setItem('doodh_watchlist', JSON.stringify(items));
    }

    function updateWatchlistBtnState() {
      var list = getWatchlist();
      $('.doodh-btn-watchlist').each(function() {
        var id = $(this).data('id');
        var exists = list.some(function(item) { return item.id == id; });
        if (exists) {
          $(this).addClass('in-watchlist').find('i').removeClass('far').addClass('fas');
          $(this).find('span').text('Saved');
        } else {
          $(this).removeClass('in-watchlist').find('i').removeClass('fas').addClass('far');
          $(this).find('span').text('Watchlist');
        }
      });
      updateWatchlistNavBadge();
    }
    updateWatchlistBtnState();

    $(document).on('click', '.doodh-btn-watchlist', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var itemData = {
        id: $btn.data('id'),
        title: $btn.data('title'),
        poster: $btn.data('poster'),
        url: $btn.data('url'),
        type: $btn.data('type') || 'movies'
      };

      var list = getWatchlist();
      var index = list.findIndex(function(x) { return x.id == itemData.id; });

      if (index > -1) {
        list.splice(index, 1);
      } else {
        list.unshift(itemData);
      }

      saveWatchlist(list);
      updateWatchlistBtnState();
      renderWatchlistPage();

      // Cloud Sync if user is logged in
      if (window.DoodhThemeData && window.DoodhThemeData.ajax_url) {
        $.post(DoodhThemeData.ajax_url, {
          action: 'doodh_toggle_watchlist',
          post_id: itemData.id,
          nonce: DoodhThemeData.nonce
        });
      }
    });

    var activeWlFilter = 'all';
    $(document).on('click', '#doodh-watchlist-type-filters button', function() {
      $('#doodh-watchlist-type-filters button').removeClass('active');
      $(this).addClass('active');
      activeWlFilter = $(this).data('wl-filter') || 'all';
      renderWatchlistPage();
    });

    $(document).on('click', '#doodh-clear-watchlist-btn', function() {
      if (confirm('Are you sure you want to clear your entire watchlist?')) {
        saveWatchlist([]);
        updateWatchlistBtnState();
        renderWatchlistPage();
      }
    });

    function renderWatchlistPage() {
      var $wlContainer = $('#doodh-watchlist-items-grid');
      if (!$wlContainer.length) return;

      var list = getWatchlist();
      $('#doodh-wl-page-count').text(list.length);

      if (!list.length) {
        $wlContainer.html('<div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:#94a3b8;"><i class="fas fa-bookmark" style="font-size:48px; margin-bottom:15px; color:#334155;"></i><h3 style="color:#fff; margin-bottom:6px;">Your Watchlist is Empty</h3><p>Browse movies and TV shows to save them to your personal streaming queue.</p><a href="' + (DoodhThemeData.home_url || '/') + '" class="doodh-btn-primary" style="margin-top:10px; display:inline-flex;"><i class="fas fa-compass"></i> Discover Titles</a></div>');
        return;
      }

      var filtered = list.filter(function(item) {
        if (activeWlFilter === 'all') return true;
        if (activeWlFilter === 'movies' && (item.type === 'movies' || item.type === 'movie')) return true;
        if (activeWlFilter === 'tvshows' && (item.type === 'tvshows' || item.type === 'tv')) return true;
        return false;
      });

      if (!filtered.length) {
        $wlContainer.html('<div style="grid-column:1/-1; text-align:center; padding:40px 20px; color:#94a3b8;"><p>No titles found in this filter.</p></div>');
        return;
      }

      var html = '';
      var fallbackPoster = DoodhThemeData.fallback_poster || '';
      $.each(filtered, function(i, item) {
        html += '<div class="doodh-card" data-id="' + item.id + '">' +
                  '<div class="doodh-card-poster-wrap">' +
                    '<img src="' + item.poster + '" class="doodh-card-poster" alt="' + item.title + '" onerror="this.onerror=null;this.src=\'' + fallbackPoster + '\';">' +
                    '<a href="' + item.url + '" class="doodh-card-overlay"><div class="doodh-play-circle"><i class="fas fa-play"></i></div></a>' +
                    '<button type="button" class="doodh-btn-watchlist in-watchlist" data-id="' + item.id + '" style="position:absolute; top:8px; right:8px; padding:4px 8px; font-size:11px; z-index:4;" title="Remove from Watchlist"><i class="fas fa-times"></i></button>' +
                  '</div>' +
                  '<div class="doodh-card-body">' +
                    '<h4 class="doodh-card-title"><a href="' + item.url + '">' + item.title + '</a></h4>' +
                  '</div>' +
                '</div>';
      });
      $wlContainer.html(html);
    }
    renderWatchlistPage();

    // 7. TV Show Seasons Tab Switcher
    $(document).on('click', '.doodh-season-tab-btn', function() {
      var seasonNum = $(this).data('season');
      $('.doodh-season-tab-btn').removeClass('active');
      $(this).addClass('active');

      $('.doodh-season-episodes-group').hide();
      $('#doodh-season-episodes-' + seasonNum).fadeIn(200);
    });

    // 8. Homepage Genre Tabs Filter
    $('.doodh-genre-tab').on('click', function() {
      if ($(this).hasClass('doodh-genre-tab-more')) return;
      $('.doodh-genre-tab').removeClass('active');
      $(this).addClass('active');

      var genre = $(this).data('genre');
      if (genre === 'all') {
        $('.doodh-filterable-card').fadeIn(200);
      } else {
        $('.doodh-filterable-card').hide();
        $('.doodh-filterable-card.genre-' + genre).fadeIn(200);
      }
    });

    // 9. FAQ Accordion Toggle
    $('.doodh-faq-question').on('click', function() {
      var $item = $(this).closest('.doodh-faq-item');
      var $answer = $item.find('.doodh-faq-answer');

      if ($item.hasClass('active')) {
        $item.removeClass('active');
        $answer.slideUp(200);
      } else {
        $('.doodh-faq-item').removeClass('active');
        $('.doodh-faq-answer').slideUp(200);
        $item.addClass('active');
        $answer.slideDown(200);
      }
    });

    // 11. Mobile Drawer Toggle
    $('#doodh-mobile-menu-btn').on('click', function(e) {
      e.preventDefault();
      $('#doodh-mobile-drawer').addClass('active');
      $('#doodh-drawer-overlay').addClass('active');
      $('body').css('overflow', 'hidden');
    });

    $('#doodh-drawer-close, #doodh-drawer-overlay').on('click', function() {
      $('#doodh-mobile-drawer').removeClass('active');
      $('#doodh-drawer-overlay').removeClass('active');
      $('body').css('overflow', '');
    });

    // 12. Update Watchlist Nav Badge Count
    function updateWatchlistNavBadge() {
      var favs = getWatchlist();
      var $badge = $('#doodh-nav-fav-count');
      if (favs.length > 0) {
        $badge.text(favs.length).show();
      } else {
        $badge.hide();
      }
    }
    updateWatchlistNavBadge();

    // 13. Production Telemetry & Analytics Event Tracker (Zero-lag Asynchronous Dispatch)
    function trackAnalyticsEvent(eventType, postId) {
      if (!window.DoodhThemeData || !window.DoodhThemeData.ajax_url) return;
      
      var targetPostId = postId || window.DoodhThemeData.current_post_id || 0;
      var payload = {
        action: 'doodh_track_event',
        event_type: eventType,
        post_id: targetPostId,
        referrer: document.referrer || ''
      };

      if (navigator.sendBeacon) {
        var formData = new FormData();
        for (var k in payload) {
          formData.append(k, payload[k]);
        }
        navigator.sendBeacon(window.DoodhThemeData.ajax_url, formData);
      } else {
        $.post(window.DoodhThemeData.ajax_url, payload);
      }
    }

    // Auto-track single movie/post pageview on load
    if (window.DoodhThemeData && window.DoodhThemeData.current_post_id > 0) {
      setTimeout(function() {
        trackAnalyticsEvent('pageview', window.DoodhThemeData.current_post_id);
      }, 800);
    }

    // Track Player stream start clicks
    $(document).on('click', '.doodh-server-btn, .doodh-play-btn, #player-iframe-box', function() {
      trackAnalyticsEvent('stream', window.DoodhThemeData.current_post_id || 0);
    });

    // Track Download button clicks
    $(document).on('click', '.doodh-download-btn, .doodh-download-box a, a[href*="download"]', function() {
      var postId = $(this).closest('[data-post-id]').data('post-id') || window.DoodhThemeData.current_post_id || 0;
      trackAnalyticsEvent('download', postId);
    });

    // Track Archive movie card clicks
    $(document).on('click', '.doodh-movie-card a, .doodh-poster-wrap a', function() {
      var postId = $(this).closest('.doodh-movie-card').data('post-id');
      if (postId) {
        trackAnalyticsEvent('click', postId);
      }
    });

    // 14. Advanced Interactive Reviews & Comments Module
    // Toggle Write Review Card
    $(document).on('click', '#doodh-toggle-review-btn', function(e) {
      e.preventDefault();
      var $form = $('#doodh-review-form-wrap');
      if ($form.is(':visible')) {
        $form.slideUp(250);
      } else {
        $form.slideDown(250, function() {
          $('html, body').animate({
            scrollTop: $form.offset().top - 100
          }, 300);
          $form.find('input[name="doodh_review_title"]').focus();
        });
      }
    });

    $(document).on('click', '#doodh-close-review-form, #doodh-cancel-review-btn', function(e) {
      e.preventDefault();
      $('#doodh-review-form-wrap').slideUp(250);
    });

    // Interactive Star Rating Picker Hover & Selection
    var starLabels = {
      10: 'Masterpiece 🔥',
      9:  'Outstanding 🌟',
      8:  'Very Good ✨',
      7:  'Good 👍',
      6:  'Decent 🙂',
      5:  'Average 😐',
      4:  'Below Average 👎',
      3:  'Poor 😕',
      2:  'Terrible 🤢',
      1:  'Unwatchable 💀'
    };

    function updateStarFeedback(val) {
      var num = parseInt(val, 10) || 10;
      var text = starLabels[num] || 'Masterpiece 🔥';
      $('#doodh-rating-display-num').text(num);
      $('#doodh-rating-display-text').text(text);
    }

    $(document).on('mouseenter', '#doodh-star-selector label', function() {
      var forId = $(this).attr('for');
      var val = $('#' + forId).val();
      updateStarFeedback(val);
    });

    $(document).on('mouseleave', '#doodh-star-selector', function() {
      var checkedVal = $('#doodh-star-selector input:checked').val() || 10;
      updateStarFeedback(checkedVal);
    });

    $(document).on('change', '#doodh-star-selector input', function() {
      updateStarFeedback($(this).val());
    });

    // Client-Side Reviews Filter (All, Critics, Audience, Star Ratings)
    $(document).on('click', '.doodh-filter-pill', function(e) {
      e.preventDefault();
      var $pill = $(this);
      var filter = $pill.data('filter');

      $('.doodh-filter-pill').removeClass('active');
      $pill.addClass('active');

      $('.doodh-breakdown-row').removeClass('is-active');
      if (filter.startsWith('star-')) {
        var starNum = filter.replace('star-', '');
        $('.doodh-breakdown-row[data-filter-star="' + starNum + '"]').addClass('is-active');
      }

      applyReviewsFilterAndSort();
    });

    // Clicking Breakdown Row in Sidebar filters reviews
    $(document).on('click', '.doodh-breakdown-row', function(e) {
      e.preventDefault();
      var star = $(this).data('filter-star');
      var filterKey = 'star-' + star;
      var $matchingPill = $('.doodh-filter-pill[data-filter="' + filterKey + '"]');

      if ($matchingPill.length) {
        $matchingPill.trigger('click');
      } else {
        $('.doodh-filter-pill').removeClass('active');
        $('.doodh-breakdown-row').removeClass('is-active');
        $(this).addClass('is-active');
        applyReviewsFilterAndSort(filterKey);
      }
    });

    // Sorting Dropdown Handler
    $(document).on('change', '#doodh-review-sort', function() {
      applyReviewsFilterAndSort();
    });

    function applyReviewsFilterAndSort(customFilter) {
      var activeFilter = customFilter || $('.doodh-filter-pill.active').data('filter') || 'all';
      var sortMethod = $('#doodh-review-sort').val() || 'highest';
      var $container = $('#doodh-reviews-grid-container');
      var $cards = $container.children('.doodh-review-card');

      if (!$cards.length) return;

      // 1. Filter visibility
      $cards.each(function() {
        var $card = $(this);
        var cardType = $card.data('type'); // 'critics' or 'community'
        var cardStar = $card.data('star'); // 'star-5', etc.

        var match = false;
        if (activeFilter === 'all') {
          match = true;
        } else if (activeFilter === 'critics' && cardType === 'critics') {
          match = true;
        } else if (activeFilter === 'community' && cardType === 'community') {
          match = true;
        } else if (activeFilter === cardStar) {
          match = true;
        }

        if (match) {
          $card.stop(true, true).fadeIn(200);
        } else {
          $card.stop(true, true).hide();
        }
      });

      // 2. Sort Cards in DOM
      var cardsArray = $cards.toArray();
      cardsArray.sort(function(a, b) {
        var $a = $(a), $b = $(b);
        if (sortMethod === 'highest') {
          return parseInt($b.data('rating'), 10) - parseInt($a.data('rating'), 10);
        } else if (sortMethod === 'newest') {
          return parseInt($b.data('date'), 10) - parseInt($a.data('date'), 10);
        } else if (sortMethod === 'helpful') {
          return parseInt($b.data('helpful'), 10) - parseInt($a.data('helpful'), 10);
        }
        return 0;
      });

      $.each(cardsArray, function(idx, item) {
        $container.append(item);
      });
    }

    // Read More / Read Less Toggle for Long Reviews
    $(document).on('click', '.doodh-read-more-toggle', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var $wrap = $btn.closest('.doodh-review-text-wrap');

      if ($wrap.hasClass('is-expanded')) {
        $wrap.removeClass('is-expanded');
        $btn.find('span').text('Read full review');
        $btn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      } else {
        $wrap.addClass('is-expanded');
        $btn.find('span').text('Show less');
        $btn.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      }
    });

    // Helpful / Upvote Button with localStorage Persistence
    function initHelpfulVotes() {
      var savedVotes = JSON.parse(localStorage.getItem('doodh_review_votes') || '{}');
      $('.doodh-helpful-btn').each(function() {
        var $btn = $(this);
        var rId = $btn.data('review-id');
        if (savedVotes[rId]) {
          $btn.addClass('is-active');
          $btn.find('i').removeClass('far').addClass('fas');
        }
      });
    }
    initHelpfulVotes();

    $(document).on('click', '.doodh-helpful-btn', function(e) {
      e.preventDefault();
      var $btn = $(this);
      var rId = $btn.data('review-id');
      var $counter = $btn.find('.doodh-helpful-counter');
      var currentCount = parseInt($counter.text(), 10) || 0;
      var savedVotes = JSON.parse(localStorage.getItem('doodh_review_votes') || '{}');

      if ($btn.hasClass('is-active')) {
        // Toggle off
        $btn.removeClass('is-active');
        $btn.find('i').removeClass('fas').addClass('far');
        $counter.text(Math.max(0, currentCount - 1));
        delete savedVotes[rId];
      } else {
        // Toggle on
        $btn.addClass('is-active');
        $btn.find('i').removeClass('far').addClass('fas');
        $counter.text(currentCount + 1);
        savedVotes[rId] = true;
      }
      localStorage.setItem('doodh_review_votes', JSON.stringify(savedVotes));
    });

    // =========================================================================
    // 15. User Authentication, Account Dropdown & Auth Modal
    // =========================================================================
    function openAuthModal(tab) {
      tab = tab || 'login';
      var $modal = $('#doodh-auth-modal');
      if (!$modal.length) return;

      switchAuthTab(tab);
      $('#doodh-auth-feedback').hide().removeClass('success error').text('');
      $modal.fadeIn(200);
      $('body').css('overflow', 'hidden');
    }

    function closeAuthModal() {
      $('#doodh-auth-modal').fadeOut(200);
      $('body').css('overflow', '');
    }

    function switchAuthTab(tab) {
      $('.doodh-auth-tab-btn').removeClass('active');
      $('.doodh-auth-tab-btn[data-auth-tab="' + tab + '"]').addClass('active');
      $('.doodh-auth-tab-pane').removeClass('active');
      $('#doodh-tab-' + tab).addClass('active');
      $('#doodh-auth-feedback').hide().text('');
    }

    // Trigger Auth Modal from anywhere
    $(document).on('click', '.doodh-auth-trigger', function(e) {
      e.preventDefault();
      var tab = $(this).data('auth-mode') || 'login';
      openAuthModal(tab);
    });

    $(document).on('click', '#doodh-auth-modal-close', function(e) {
      e.preventDefault();
      closeAuthModal();
    });

    $(document).on('click', '#doodh-auth-modal', function(e) {
      if ($(e.target).is('#doodh-auth-modal')) {
        closeAuthModal();
      }
    });

    $(document).on('click', '.doodh-auth-tab-btn', function(e) {
      e.preventDefault();
      var tab = $(this).data('auth-tab');
      switchAuthTab(tab);
    });

    $(document).on('click', '#doodh-trigger-forgot', function(e) {
      e.preventDefault();
      switchAuthTab('forgot');
    });

    $(document).on('click', '#doodh-back-to-login', function(e) {
      e.preventDefault();
      switchAuthTab('login');
    });

    // Password Visibility Toggle
    $(document).on('click', '.doodh-pw-toggle', function(e) {
      e.preventDefault();
      var $input = $(this).siblings('input');
      var $icon = $(this).find('i');
      if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        $input.attr('type', 'password');
        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
      }
    });

    // Registration Avatar Selector
    $(document).on('click', '.doodh-reg-av-thumb', function(e) {
      e.preventDefault();
      $('.doodh-reg-av-thumb').removeClass('selected');
      $(this).addClass('selected');
      var avatarUrl = $(this).data('avatar');
      $('#doodh-reg-avatar-preview-img').attr('src', avatarUrl);
      $('#doodh-reg-avatar-input').val(avatarUrl);
    });

    $(document).on('click', '#doodh-reg-random-avatar', function(e) {
      e.preventDefault();
      var randomSeed = 'Viewer_' + Math.floor(Math.random() * 9999);
      var randomUrl = 'https://api.dicebear.com/9.x/adventurer/svg?seed=' + encodeURIComponent(randomSeed);
      $('.doodh-reg-av-thumb').removeClass('selected');
      $('#doodh-reg-avatar-preview-img').attr('src', randomUrl);
      $('#doodh-reg-avatar-input').val(randomUrl);
    });

    // User Header Dropdown Toggle
    $(document).on('click', '#doodh-user-dropdown-btn', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $('#doodh-user-menu-wrap').toggleClass('active');
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('#doodh-user-menu-wrap').length) {
        $('#doodh-user-menu-wrap').removeClass('active');
      }
    });

    // AJAX Login Form Submission
    $('#doodh-login-form').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('.doodh-auth-submit-btn');
      var $feedback = $('#doodh-auth-feedback');

      $btn.prop('disabled', true).find('span').text('Signing In...');
      $feedback.hide().removeClass('success error');

      var formData = $form.serializeArray();
      formData.push({ name: 'action', value: 'doodh_login' });
      formData.push({ name: 'nonce', value: DoodhThemeData.nonce });

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(res) {
          if (res.success) {
            $feedback.addClass('success').text(res.data.message).fadeIn(200);
            
            // Sync local watchlist to cloud
            var localWl = getWatchlist().map(function(item) { return item.id; });
            if (localWl.length > 0) {
              $.post(DoodhThemeData.ajax_url, {
                action: 'doodh_merge_watchlist',
                client_items: localWl,
                nonce: DoodhThemeData.nonce
              });
            }

            setTimeout(function() {
              window.location.reload();
            }, 800);
          } else {
            $feedback.addClass('error').text(res.data.message || 'Login failed. Please check your credentials.').fadeIn(200);
            $btn.prop('disabled', false).find('span').text('Sign In');
          }
        },
        error: function() {
          $feedback.addClass('error').text('An unexpected network error occurred.').fadeIn(200);
          $btn.prop('disabled', false).find('span').text('Sign In');
        }
      });
    });

    // AJAX Register Form Submission
    $('#doodh-register-form').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('.doodh-auth-submit-btn');
      var $feedback = $('#doodh-auth-feedback');

      $btn.prop('disabled', true).find('span').text('Creating Account...');
      $feedback.hide().removeClass('success error');

      var formData = $form.serializeArray();
      formData.push({ name: 'action', value: 'doodh_register' });
      formData.push({ name: 'nonce', value: DoodhThemeData.nonce });

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(res) {
          if (res.success) {
            $feedback.addClass('success').text(res.data.message).fadeIn(200);

            // Sync local watchlist to new account
            var localWl = getWatchlist().map(function(item) { return item.id; });
            if (localWl.length > 0) {
              $.post(DoodhThemeData.ajax_url, {
                action: 'doodh_merge_watchlist',
                client_items: localWl,
                nonce: DoodhThemeData.nonce
              });
            }

            setTimeout(function() {
              window.location.reload();
            }, 900);
          } else {
            $feedback.addClass('error').text(res.data.message || 'Registration failed.').fadeIn(200);
            $btn.prop('disabled', false).find('span').text('Create Free Account');
          }
        },
        error: function() {
          $feedback.addClass('error').text('An unexpected network error occurred.').fadeIn(200);
          $btn.prop('disabled', false).find('span').text('Create Free Account');
        }
      });
    });

    // AJAX Forgot Password Form Submission
    $('#doodh-forgot-form').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('.doodh-auth-submit-btn');
      var $feedback = $('#doodh-auth-feedback');

      $btn.prop('disabled', true).find('span').text('Sending...');
      $feedback.hide().removeClass('success error');

      var formData = $form.serializeArray();
      formData.push({ name: 'action', value: 'doodh_forgot_password' });
      formData.push({ name: 'nonce', value: DoodhThemeData.nonce });

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(res) {
          if (res.success) {
            $feedback.addClass('success').text(res.data.message).fadeIn(200);
            $form[0].reset();
          } else {
            $feedback.addClass('error').text(res.data.message).fadeIn(200);
          }
          $btn.prop('disabled', false).find('span').text('Send Reset Instructions');
        },
        error: function() {
          $feedback.addClass('error').text('Unable to process password reset.').fadeIn(200);
          $btn.prop('disabled', false).find('span').text('Send Reset Instructions');
        }
      });
    });

    // =========================================================================
    // 16. Movie & TV Show Request System
    // =========================================================================
    // Request Tab Switcher
    $(document).on('click', '.doodh-req-tab-btn', function() {
      var tab = $(this).data('req-tab');
      $('.doodh-req-tab-btn').removeClass('active');
      $(this).addClass('active');

      $('.doodh-req-pane').hide();
      $('#doodh-req-pane-' + tab).fadeIn(200);

      if (tab === 'tracking') {
        loadUserRequests();
      }
    });

    // Submit Request AJAX Form
    $('#doodh-request-form').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $('#doodh-request-submit-btn');
      var $feedback = $('#doodh-request-feedback');

      $btn.prop('disabled', true).find('span').text('Submitting...');
      $feedback.hide();

      var formData = $form.serializeArray();
      formData.push({ name: 'action', value: 'doodh_submit_request' });
      formData.push({ name: 'nonce', value: DoodhThemeData.nonce });

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function(res) {
          if (res.success) {
            $feedback.css({
              'display': 'block',
              'background': 'rgba(16, 185, 129, 0.15)',
              'border': '1px solid rgba(16, 185, 129, 0.35)',
              'color': '#10b981'
            }).html('<i class="fas fa-check-circle"></i> ' + res.data.message);
            $form[0].reset();
          } else {
            $feedback.css({
              'display': 'block',
              'background': 'rgba(239, 68, 68, 0.15)',
              'border': '1px solid rgba(239, 68, 68, 0.35)',
              'color': '#ef4444'
            }).html('<i class="fas fa-exclamation-circle"></i> ' + res.data.message);
          }
          $btn.prop('disabled', false).find('span').text('Submit Streaming Request');
        },
        error: function() {
          $feedback.css({
            'display': 'block',
            'background': 'rgba(239, 68, 68, 0.15)',
            'border': '1px solid rgba(239, 68, 68, 0.35)',
            'color': '#ef4444'
          }).html('<i class="fas fa-exclamation-circle"></i> An unexpected network error occurred.');
          $btn.prop('disabled', false).find('span').text('Submit Streaming Request');
        }
      });
    });

    // Load User's Submitted Requests
    function loadUserRequests() {
      var $list = $('#doodh-user-requests-list');
      if (!$list.length) return;

      $.ajax({
        url: DoodhThemeData.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'doodh_get_user_requests',
          nonce: DoodhThemeData.nonce
        },
        success: function(res) {
          if (res.success && res.data.requests && res.data.requests.length) {
            var html = '';
            $.each(res.data.requests, function(i, req) {
              var badgeClass = 'doodh-req-status-pending';
              var badgeIcon = '<i class="fas fa-hourglass-half"></i>';
              var badgeText = 'Pending Review';

              if (req.status === 'in_progress') {
                badgeClass = 'doodh-req-status-progress';
                badgeIcon = '<i class="fas fa-cog fa-spin"></i>';
                badgeText = 'In Progress';
              } else if (req.status === 'completed') {
                badgeClass = 'doodh-req-status-completed';
                badgeIcon = '<i class="fas fa-check-circle"></i>';
                badgeText = 'Available to Watch';
              } else if (req.status === 'declined') {
                badgeClass = 'doodh-req-status-declined';
                badgeIcon = '<i class="fas fa-times-circle"></i>';
                badgeText = 'Declined';
              }

              html += '<div class="doodh-my-req-card">' +
                        '<div>' +
                          '<strong style="font-size:16px; color:#fff; display:block; margin-bottom:4px;">' + req.title + (req.year ? ' (' + req.year + ')' : '') + '</strong>' +
                          '<span style="font-size:12px; color:#94a3b8;"><i class="fas fa-tag"></i> ' + req.type + ' &bull; <i class="far fa-calendar-alt"></i> ' + req.date + '</span>' +
                          (req.admin_note ? '<div style="margin-top:6px; font-size:12px; color:#38bdf8; background:rgba(56,189,248,0.1); padding:4px 8px; border-radius:4px; display:inline-block;"><i class="fas fa-info-circle"></i> ' + req.admin_note + '</div>' : '') +
                        '</div>' +
                        '<div style="display:flex; align-items:center; gap:10px;">' +
                          '<span class="doodh-req-status-pill ' + badgeClass + '">' + badgeIcon + ' ' + badgeText + '</span>' +
                          (req.link_url ? '<a href="' + req.link_url + '" class="doodh-btn-primary" style="padding:6px 14px; font-size:12px;"><i class="fas fa-play"></i> Watch Now</a>' : '') +
                        '</div>' +
                      '</div>';
            });
            $list.html(html);
          } else {
            $list.html('<div style="text-align:center; padding:40px 20px; color:#94a3b8;"><i class="fas fa-paper-plane" style="font-size:36px; color:#334155; margin-bottom:12px;"></i><h4 style="color:#fff;">No Requests Submitted Yet</h4><p>When you request a movie or series, track its progress and get stream links here.</p><button type="button" class="doodh-btn-primary" onclick="$(\'.doodh-req-tab-btn[data-req-tab=\\\'submit\\\']\').click();"><i class="fas fa-plus-circle"></i> Submit Your First Request</button></div>');
          }
        },
        error: function() {
          $list.html('<div style="text-align:center; padding:30px; color:#ef4444;"><p>Failed to load requests. Please try again.</p></div>');
        }
      });
    }

    // Footer Back to Top Button
    $(document).on('click', '#doodh-footer-backtotop', function(e) {
      e.preventDefault();
      $('html, body').animate({ scrollTop: 0 }, 400);
    });

  });
})(jQuery);



