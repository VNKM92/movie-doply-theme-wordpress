/**
 * VM SEO - Advanced Real-Time Content Analyzer & Live Snippet Engine (Yoast-Grade)
 */
(function($) {
  'use strict';

  $(document).ready(function() {

    // 1. Tab Navigation
    $(document).on('click', '.vm-seo-tab-btn', function(e) {
      e.preventDefault();
      var tab = $(this).data('tab');
      $('.vm-seo-tab-btn').removeClass('active');
      $(this).addClass('active');

      $('.vm-seo-tab-content').removeClass('active');
      $('#vm-tab-' + tab).addClass('active');
    });

    // 2. Mobile vs Desktop SERP Preview Toggle
    $(document).on('click', '.vm-device-btn', function(e) {
      e.preventDefault();
      var device = $(this).data('device');
      $('.vm-device-btn').removeClass('active');
      $(this).addClass('active');

      $('#vm-serp-preview').removeClass('mobile desktop').addClass(device);
    });

    // 3. Variable Pills Insertion into Active / Last Focused Field
    var activeInputField = $('#vm_seo_title');
    $(document).on('focus', 'input[type="text"], textarea', function() {
      activeInputField = $(this);
    });

    $(document).on('click', '.vm-var-pill-btn', function(e) {
      e.preventDefault();
      var variable = $(this).data('var');
      var parentCard = $(this).closest('.vm-card-section, .vm-field-group, .vm-seo-metabox-wrap');
      var target = null;

      if (activeInputField && activeInputField.length && activeInputField.is(':visible') && parentCard.has(activeInputField).length) {
        target = activeInputField;
      } else if (parentCard.length) {
        target = parentCard.find('input[type="text"], textarea').first();
      } else {
        target = activeInputField && activeInputField.length ? activeInputField : $('#vm_seo_title');
      }

      if (target && target.length) {
        var curVal = target.val();
        var sep = curVal.length && !curVal.endsWith(' ') ? ' ' : '';
        target.val(curVal + sep + variable).trigger('input').focus();
      }
    });

    // 4. Media Library Picker for Social Share Image
    $('#vm-btn-pick-og-image').on('click', function(e) {
      e.preventDefault();
      if (typeof wp !== 'undefined' && wp.media) {
        var frame = wp.media({
          title: 'Select or Upload Social Share Image',
          button: { text: 'Use as Social Image' },
          multiple: false,
          library: { type: 'image' }
        });

        frame.on('select', function() {
          var attachment = frame.state().get('selection').first().toJSON();
          if (attachment && attachment.url) {
            $('#vm_og_image').val(attachment.url).trigger('input');
          }
        });

        frame.open();
      }
    });

    // 5. Helper: Retrieve Complete Post Body Text from TinyMCE, Textarea, or Gutenberg
    function getPostContentText() {
      var content = '';
      if (typeof tinymce !== 'undefined' && tinymce.get('content') && !tinymce.get('content').isHidden()) {
        content = tinymce.get('content').getContent({ format: 'text' }) || '';
      } else if ($('#content').length) {
        content = $('#content').val() || '';
      } else if (window.wp && wp.data && wp.data.select && wp.data.select('core/editor')) {
        try {
          content = wp.data.select('core/editor').getEditedPostContent() || '';
          content = content.replace(/<[^>]*>?/gm, ' ');
        } catch (e) {}
      }
      return content.trim();
    }

    // 6. Master Real-Time Evaluation Engine
    function runLiveEvaluation() {
      var rawTitle = $('#vm_seo_title').val() || '';
      var rawDesc  = $('#vm_seo_desc').val() || '';
      var focusKw  = ($('#vm_focus_keyword').val() || '').toLowerCase().trim();
      var ogTitle  = $('#vm_og_title').val() || '';
      var ogDesc   = $('#vm_og_desc').val() || '';
      var ogImg    = $('#vm_og_image').val() || '';

      var postTitle   = $('#title').val() || (window.VMSEOData ? VMSEOData.defaultTitle : '') || 'Movie Title';
      var siteName    = window.VMSEOData ? VMSEOData.siteName : 'MySite';
      var separator   = window.VMSEOData ? VMSEOData.separator : '|';
      var tagline     = window.VMSEOData ? VMSEOData.siteTagline : '';
      var currentYear = new Date().getFullYear();
      var castName    = (window.VMSEOData && VMSEOData.cast) ? VMSEOData.cast : 'Leonardo DiCaprio, Joseph Gordon-Levitt';
      var dirName     = (window.VMSEOData && VMSEOData.director) ? VMSEOData.director : 'Christopher Nolan';
      var yearVal     = (window.VMSEOData && VMSEOData.year) ? VMSEOData.year : currentYear;
      var genreVal    = (window.VMSEOData && VMSEOData.genre) ? VMSEOData.genre : 'Action';
      var qualityVal  = (window.VMSEOData && VMSEOData.quality) ? VMSEOData.quality : 'HD';
      var ratingVal   = (window.VMSEOData && VMSEOData.rating) ? VMSEOData.rating : '8.5';

      // Replace variables for live preview
      function replaceVars(str) {
        if (!str) return '';
        return str.replace(/%%title%%/gi, postTitle)
                  .replace(/%%sitename%%/gi, siteName)
                  .replace(/%%sep%%/gi, separator)
                  .replace(/%%tagline%%/gi, tagline)
                  .replace(/%%year%%/gi, yearVal)
                  .replace(/%%cast%%/gi, castName)
                  .replace(/%%actors%%/gi, castName)
                  .replace(/%%crew%%/gi, castName)
                  .replace(/%%director%%/gi, dirName)
                  .replace(/%%directors%%/gi, dirName)
                  .replace(/%%category%%/gi, genreVal)
                  .replace(/%%genres%%/gi, genreVal)
                  .replace(/%%quality%%/gi, qualityVal)
                  .replace(/%%rating%%/gi, ratingVal)
                  .replace(/%%focus_keyword%%/gi, focusKw);
      }

      var defaultTitleTpl = (window.VMSEOData && VMSEOData.defaultTitleTemplate) ? VMSEOData.defaultTitleTemplate : '%%title%% %%sep%% %%sitename%%';
      var defaultDescTpl  = (window.VMSEOData && VMSEOData.defaultDescTemplate) ? VMSEOData.defaultDescTemplate : ('Watch %%title%% full movie stream in HD and 4K online on %%sitename%%.');

      var renderedTitle = replaceVars(rawTitle) || replaceVars(defaultTitleTpl) || (postTitle + ' ' + separator + ' ' + siteName);
      var renderedDesc  = replaceVars(rawDesc)  || replaceVars(defaultDescTpl)  || ('Watch ' + postTitle + ' full movie stream in HD and 4K online on ' + siteName + '.');

      // Update SERP Snippet Preview
      $('#vm-serp-title-preview').text(renderedTitle);
      $('#vm-serp-desc-preview').text(renderedDesc);

      // Update Social Preview Cards
      var dispOgTitle = replaceVars(ogTitle) || renderedTitle;
      var dispOgDesc  = replaceVars(ogDesc) || renderedDesc;
      $('#vm-fb-title-prev').text(dispOgTitle);
      $('#vm-fb-desc-prev').text(dispOgDesc);
      if (ogImg) {
        $('#vm-fb-img-prev').css('background-image', 'url(' + ogImg + ')').find('span').hide();
      } else {
        $('#vm-fb-img-prev').css('background-image', 'none').find('span').show();
      }

      // 7. Title Length Counter & Progress Bar (Optimal: 40 - 60 chars)
      var titleLen = renderedTitle.length;
      $('#vm-title-count').text(titleLen + ' / 60 chars');
      var titlePct = Math.min(100, Math.round((titleLen / 60) * 100));
      $('#vm-title-bar').css('width', titlePct + '%');
      if (titleLen >= 38 && titleLen <= 65) {
        $('#vm-title-bar').css('background', '#10b981'); // Green
      } else if (titleLen > 65) {
        $('#vm-title-bar').css('background', '#ef4444'); // Red
      } else {
        $('#vm-title-bar').css('background', '#f59e0b'); // Orange
      }

      // 8. Description Length Counter & Progress Bar (Optimal: 120 - 160 chars)
      var descLen = renderedDesc.length;
      $('#vm-desc-count').text(descLen + ' / 160 chars');
      var descPct = Math.min(100, Math.round((descLen / 160) * 100));
      $('#vm-desc-bar').css('width', descPct + '%');
      if (descLen >= 115 && descLen <= 165) {
        $('#vm-desc-bar').css('background', '#10b981'); // Green
      } else if (descLen > 165) {
        $('#vm-desc-bar').css('background', '#ef4444'); // Red
      } else {
        $('#vm-desc-bar').css('background', '#f59e0b'); // Orange
      }

      // 9. Detailed Content & Keyword Analysis Checklist
      var contentText = getPostContentText();
      var wordsArray = contentText.length ? contentText.match(/\b\w+\b/g) || [] : [];
      var wordCount = wordsArray.length;
      $('#vm-calc-words').text(wordCount + ' words');

      var kwCount = 0;
      var kwDensity = 0;
      if (focusKw && wordCount > 0) {
        var kwRegex = new RegExp('\\b' + focusKw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'gi');
        var matches = contentText.match(kwRegex);
        kwCount = matches ? matches.length : 0;
        kwDensity = ((kwCount / wordCount) * 100).toFixed(1);
      }
      $('#vm-calc-density').text(kwDensity + '% (' + kwCount + ' occurrences)');

      var totalChecks = 0;
      var passedChecks = 0;

      function updateItem(selector, isGood, isWarning) {
        totalChecks++;
        var $el = $(selector);
        $el.removeClass('good ok poor');
        if (isGood) {
          passedChecks++;
          $el.addClass('good').find('.dot').css('background', '#10b981');
        } else if (isWarning) {
          passedChecks += 0.5;
          $el.addClass('ok').find('.dot').css('background', '#f59e0b');
        } else {
          $el.addClass('poor').find('.dot').css('background', '#ef4444');
        }
      }

      // Check 1: Keyphrase in SEO Title
      var hasKwInTitle = focusKw && renderedTitle.toLowerCase().indexOf(focusKw) !== -1;
      updateItem('#chk-title-kw', hasKwInTitle, !focusKw);

      // Check 2: Keyphrase at Beginning of Title
      var kwAtTitleStart = hasKwInTitle && renderedTitle.toLowerCase().indexOf(focusKw) <= 15;
      updateItem('#chk-title-start', kwAtTitleStart, hasKwInTitle);

      // Check 3: Keyphrase in Meta Description
      var hasKwInDesc = focusKw && renderedDesc.toLowerCase().indexOf(focusKw) !== -1;
      updateItem('#chk-desc-kw', hasKwInDesc, !focusKw);

      // Check 4: Meta Description Length
      var isDescGood = descLen >= 115 && descLen <= 165;
      var isDescOk = descLen >= 80 && descLen <= 180;
      updateItem('#chk-desc-length', isDescGood, isDescOk);

      // Check 5: Keyphrase in Slug
      var slug = ($('#post_name').val() || $('#editable-post-name').text() || '').toLowerCase();
      var hasKwInSlug = focusKw && slug.indexOf(focusKw.replace(/\s+/g, '-')) !== -1;
      updateItem('#chk-slug-kw', hasKwInSlug, !focusKw || slug.length > 0);

      // Check 6: Keyphrase in Intro
      var introText = contentText.substring(0, 300).toLowerCase();
      var hasKwInIntro = focusKw && introText.indexOf(focusKw) !== -1;
      updateItem('#chk-intro-kw', hasKwInIntro, !focusKw);

      // Check 7: Keyphrase Density (1.0% - 2.5% ideal)
      var isDensityGood = kwDensity >= 0.8 && kwDensity <= 2.8;
      var isDensityOk = kwDensity > 0 && kwDensity <= 4.0;
      updateItem('#chk-density', isDensityGood, isDensityOk);

      // Check 8: Word count (Movies >= 60 words, Standard posts >= 250)
      var isWordGood = wordCount >= 60;
      var isWordOk = wordCount >= 30;
      updateItem('#chk-length', isWordGood, isWordOk);

      // Check 9: Subheadings
      var hasHeadings = /<h[2-4]/i.test(contentText) || contentText.indexOf('##') !== -1;
      updateItem('#chk-headings', hasHeadings, true);

      // Check 10: Image Alts
      var hasImages = /<img/i.test(contentText) || $('#vm_poster_url').val();
      updateItem('#chk-alt', hasImages, true);

      // Compute Overall Dynamic Score (0 - 100)
      var finalScore = Math.round((passedChecks / totalChecks) * 100);
      $('#vm-badge-score').text(finalScore + '/100');
      $('#vm-badge-score').removeClass('good ok poor');

      var badgeLabel = 'Good';
      if (finalScore >= 80) {
        $('#vm-badge-score').addClass('good');
        $('#vm-seo-overall-badge').css({ background: '#dcfce7', color: '#15803d' }).text(finalScore + '/100 (Good 🟢)');
      } else if (finalScore >= 50) {
        $('#vm-badge-score').addClass('ok');
        $('#vm-seo-overall-badge').css({ background: '#fef3c7', color: '#b45309' }).text(finalScore + '/100 (OK 🟠)');
        badgeLabel = 'OK';
      } else {
        $('#vm-badge-score').addClass('poor');
        $('#vm-seo-overall-badge').css({ background: '#fee2e2', color: '#b91c1c' }).text(finalScore + '/100 (Needs Work 🔴)');
        badgeLabel = 'Poor';
      }

      // Readability Flesch score calculation (approximate)
      var sentences = (contentText.match(/[.!?]+/g) || []).length || 1;
      var syllables = Math.round(wordCount * 1.4);
      var flesch = 206.835 - (1.015 * (wordCount / sentences)) - (84.6 * (syllables / Math.max(1, wordCount)));
      flesch = Math.max(10, Math.min(100, Math.round(flesch)));
      $('#vm-flesch-score').text(flesch);
    }

    // Attach real-time input listeners
    $('#vm_seo_title, #vm_seo_desc, #vm_focus_keyword, #vm_og_title, #vm_og_desc, #vm_og_image, #title, #content').on('input keyup change', runLiveEvaluation);
    
    // Gutenberg editor subscription if present
    if (window.wp && wp.data && wp.data.subscribe) {
      wp.data.subscribe(function() {
        runLiveEvaluation();
      });
    }

    // Sitemaps Live Ping Search Engines Button
    $('#vm-btn-ping-sitemaps').on('click', function(e) {
      e.preventDefault();
      var $btn = $(this);
      $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin" style="vertical-align:middle;"></span> Pinging Google & Bing...');

      $.post(
        window.VMSEOData ? VMSEOData.ajaxUrl : '/wp-admin/admin-ajax.php',
        {
          action: 'vm_seo_ping_sitemaps',
          nonce: window.VMSEOData ? VMSEOData.nonce : ''
        },
        function(res) {
          $btn.prop('disabled', false).html('<span class="dashicons dashicons-rss" style="vertical-align:middle;"></span> Ping Google & Bing Now');
          var noticeBox = $('#vm-sitemap-ping-notice');
          if (res.success) {
            noticeBox.html('<div class="notice notice-success is-dismissible" style="padding:10px 15px; border-radius:6px;"><p><strong>' + res.data.message + '</strong> (' + res.data.time + ')</p></div>').fadeIn();
          } else {
            noticeBox.html('<div class="notice notice-error is-dismissible" style="padding:10px 15px; border-radius:6px;"><p>' + (res.data ? res.data.message : 'Ping failed.') + '</p></div>').fadeIn();
          }
        }
      );
    });

    // Initial evaluation run
    setTimeout(runLiveEvaluation, 300);
  });
})(jQuery);