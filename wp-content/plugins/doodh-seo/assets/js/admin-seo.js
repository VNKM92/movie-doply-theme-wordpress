/**
 * Doodh SEO - Live Snippet & Keyword Density Analyzer JavaScript
 */
(function($) {
  'use strict';

  $(document).ready(function() {

    // 1. Tab Switcher
    $(document).on('click', '.doodh-seo-tab-btn', function(e) {
      e.preventDefault();
      var tab = $(this).data('tab');
      $('.doodh-seo-tab-btn').removeClass('active');
      $(this).addClass('active');

      $('.doodh-seo-tab-content').removeClass('active');
      $('#doodh-tab-' + tab).addClass('active');
    });

    // 2. Mobile vs Desktop SERP Preview Toggle
    $(document).on('click', '.doodh-device-btn', function(e) {
      e.preventDefault();
      var device = $(this).data('device');
      $('.doodh-device-btn').removeClass('active');
      $(this).addClass('active');

      $('#doodh-serp-preview').removeClass('mobile desktop').addClass(device);
    });

    // 3. Live Snippet & Character Count Updating
    function updateSnippet() {
      var title = $('#doodh_seo_title').val() || DoodhSEOData.defaultTitle || DoodhSEOData.siteName;
      var desc  = $('#doodh_seo_desc').val() || DoodhSEOData.defaultExcerpt || 'Watch full movie online in HD.';
      var focusKw = $('#doodh_focus_keyword').val().toLowerCase().trim();

      // Replace variables in preview
      title = title.replace(/%%title%%/g, DoodhSEOData.defaultTitle)
                   .replace(/%%sitename%%/g, DoodhSEOData.siteName)
                   .replace(/%%sep%%/g, DoodhSEOData.separator);

      $('#doodh-serp-title-preview').text(title);
      $('#doodh-serp-desc-preview').text(desc);

      // Title Counter & Progress Bar (Target: 40 - 60 chars)
      var titleLen = title.length;
      $('#doodh-title-count').text(titleLen + ' / 60 chars');
      var titlePercent = Math.min(100, (titleLen / 60) * 100);
      $('#doodh-title-bar').css('width', titlePercent + '%');
      if (titleLen >= 35 && titleLen <= 65) {
        $('#doodh-title-bar').css('background', '#10b981'); // Green
      } else if (titleLen > 65) {
        $('#doodh-title-bar').css('background', '#ef4444'); // Red
      } else {
        $('#doodh-title-bar').css('background', '#f59e0b'); // Orange
      }

      // Desc Counter & Progress Bar (Target: 120 - 160 chars)
      var descLen = desc.length;
      $('#doodh-desc-count').text(descLen + ' / 160 chars');
      var descPercent = Math.min(100, (descLen / 160) * 100);
      $('#doodh-desc-bar').css('width', descPercent + '%');
      if (descLen >= 110 && descLen <= 165) {
        $('#doodh-desc-bar').css('background', '#10b981');
      } else if (descLen > 165) {
        $('#doodh-desc-bar').css('background', '#ef4444');
      } else {
        $('#doodh-desc-bar').css('background', '#f59e0b');
      }

      // Check Keyword in Title
      if (focusKw && title.toLowerCase().indexOf(focusKw) !== -1) {
        $('#chk-title-kw .dot').css('background', '#10b981');
      } else if (focusKw) {
        $('#chk-title-kw .dot').css('background', '#ef4444');
      }

      // Check Keyword in Description
      if (focusKw && desc.toLowerCase().indexOf(focusKw) !== -1) {
        $('#chk-desc-kw .dot').css('background', '#10b981');
      } else if (focusKw) {
        $('#chk-desc-kw .dot').css('background', '#ef4444');
      }
    }

    $('#doodh_seo_title, #doodh_seo_desc, #doodh_focus_keyword').on('input', updateSnippet);
    updateSnippet();
  });
})(jQuery);