/**
 * DoodhTheme - Production Streaming JavaScript
 * Server switching, Live AJAX Search, Star Rating, Watchlist, Genre Tabs, FAQ, and Request Form
 */

(function($) {
  'use strict';

  $(document).ready(function() {

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

    // 4. Live AJAX Search with Debounce (Desktop & Mobile)
    var searchTimer = null;

    function handleLiveSearch($input, $results) {
      var keyword = $input.val().trim();
      clearTimeout(searchTimer);

      if (keyword.length < 2) {
        $results.hide().empty();
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
            nonce: DoodhThemeData.nonce
          },
          beforeSend: function() {
            $results.html('<div style="padding:15px; text-align:center; color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> ' + DoodhThemeData.searching + '</div>').show();
          },
          success: function(response) {
            if (response.success && response.data.results.length > 0) {
              var html = '';
              $.each(response.data.results, function(i, item) {
                var fallbackPoster = DoodhThemeData.fallback_poster || '';
                html += '<a href="' + item.url + '" class="doodh-live-item">' +
                          '<img src="' + item.poster + '" class="doodh-live-poster" alt="' + item.title + '" onerror="this.onerror=null;this.src=\'' + fallbackPoster + '\';">' +
                          '<div class="doodh-live-info">' +
                            '<div class="doodh-live-title">' + item.title + '</div>' +
                            '<div class="doodh-live-meta">' +
                              '<span class="doodh-live-tag">' + item.type + '</span>' +
                              '<span>' + item.year + '</span>' +
                              '<span class="doodh-live-rating"><i class="fas fa-star"></i> ' + item.rating + '</span>' +
                            '</div>' +
                          '</div>' +
                        '</a>';
              });
              html += '<a href="' + response.data.more_url + '" style="display:block; padding:10px; text-align:center; font-size:13px; font-weight:700; color:#e50914; background:rgba(0,0,0,0.4);">' + DoodhThemeData.view_all + ' &rarr;</a>';
              $results.html(html).show();
            } else {
              $results.html('<div style="padding:15px; text-align:center; color:#94a3b8;">' + DoodhThemeData.no_results + '</div>').show();
            }
          }
        });
      }, 300);
    }

    // Desktop Input Handler
    $('#doodh-live-search-input').on('input', function() {
      handleLiveSearch($(this), $('#doodh-live-results-box'));
    });

    // Mobile Input Handler
    $('#doodh-mobile-search-input').on('input', function() {
      handleLiveSearch($(this), $('#doodh-mobile-live-results-box'));
    });

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
      if (!$(e.target).closest('.doodh-search-box, .doodh-mobile-search-bar').length) {
        $('#doodh-live-results-box, #doodh-mobile-live-results-box').hide();
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

    // 6. Watchlist LocalStorage System
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
        type: $btn.data('type')
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
    });

    function renderWatchlistPage() {
      var $wlContainer = $('#doodh-watchlist-items-grid');
      if (!$wlContainer.length) return;

      var list = getWatchlist();
      if (!list.length) {
        $wlContainer.html('<div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:#94a3b8;"><i class="fas fa-bookmark" style="font-size:48px; margin-bottom:15px; color:#334155;"></i><h3>Your Watchlist is Empty</h3><p>Browse movies and TV shows to save them here for later.</p></div>');
        return;
      }

      var html = '';
      var fallbackPoster = DoodhThemeData.fallback_poster || '';
      $.each(list, function(i, item) {
        html += '<div class="doodh-card" data-id="' + item.id + '">' +
                  '<div class="doodh-card-poster-wrap">' +
                    '<img src="' + item.poster + '" class="doodh-card-poster" alt="' + item.title + '" onerror="this.onerror=null;this.src=\'' + fallbackPoster + '\';">' +
                    '<a href="' + item.url + '" class="doodh-card-overlay"><div class="doodh-play-circle"><i class="fas fa-play"></i></div></a>' +
                    '<button type="button" class="doodh-btn-watchlist in-watchlist" data-id="' + item.id + '" style="position:absolute; top:8px; right:8px; padding:4px 8px; font-size:11px; z-index:4;"><i class="fas fa-times"></i></button>' +
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

  });
})(jQuery);
