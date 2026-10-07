/* Homepage content editor (Dashboard > Homepage content).
   Tabs between sections, repeatable rows (add / remove / move), the Media Library photo
   picker and live icon previews. Without this script every section is shown on one long
   page and the form still saves. */
(function ($) {
  'use strict';
  var $editor = $('.aaykay-editor');
  if (!$editor.length) return;

  /* Tabs: show one section at a time; remember the last one in the address (#ak-hero). */
  var $tabs = $editor.find('.ak-tabs a[data-tab]');
  var $sections = $editor.find('.ak-section');
  function show(id) {
    if (!$sections.filter('[data-section="' + id + '"]').length) id = $sections.first().data('section');
    $sections.attr('hidden', true).filter('[data-section="' + id + '"]').removeAttr('hidden');
    $tabs.removeAttr('aria-current').filter('[data-tab="' + id + '"]').attr('aria-current', 'page');
  }
  $editor.addClass('ak-has-tabs');
  $tabs.on('click', function (e) {
    e.preventDefault();
    var id = $(this).data('tab');
    show(id);
    history.replaceState(null, '', '#ak-' + id);
    // Keep the open tab after saving: options.php returns to the page without the hash.
    try { sessionStorage.setItem('aaykay-editor-tab', id); } catch (_) {}
  });
  var start = (location.hash || '').replace('#ak-', '');
  if (!start) { try { start = sessionStorage.getItem('aaykay-editor-tab') || ''; } catch (_) {} }
  show(start);

  /* Warn before leaving with unsaved changes. */
  var dirty = false;
  $editor.on('input change', '.ak-form :input', function () { dirty = true; });
  $editor.on('submit', '.ak-form', function () { dirty = false; });
  window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

  /* Icons: preview follows the select. */
  $editor.on('change', '.ak-icon-pick select', function () {
    $(this).siblings('svg').find('use').attr('href', AAYKAY_EDITOR.sprite + '#' + this.value);
  });

  /* Lists. */
  var counter = Date.now();
  function refresh($list) {
    var $rows = $list.children('.ak-rows').children('.ak-row');
    var max = parseInt($list.data('max'), 10) || 99;
    $list.find('> p > .ak-add').prop('disabled', $rows.length >= max);
    $rows.each(function (i) {
      $(this).find('> .ak-row-bar .ak-row-title').text($list.data('item').charAt(0).toUpperCase() + $list.data('item').slice(1) + ' ' + (i + 1));
      $(this).find('> .ak-row-bar .ak-up').prop('disabled', i === 0);
      $(this).find('> .ak-row-bar .ak-down').prop('disabled', i === $rows.length - 1);
    });
  }
  $editor.find('.ak-list').each(function () { refresh($(this)); });
  $editor.on('click', '.ak-add', function () {
    var $list = $(this).closest('.ak-list');
    var html = $list.children('template.ak-row-template').html().replace(/__i__/g, 'n' + (counter++));
    var $row = $(html).appendTo($list.children('.ak-rows'));
    refresh($list);
    $row.find(':input:visible').first().trigger('focus');
    dirty = true;
  });
  $editor.on('click', '.ak-remove', function () {
    var $row = $(this).closest('.ak-row');
    var $list = $row.closest('.ak-list');
    if (!window.confirm('Remove this ' + $list.data('item') + '? It disappears from the website when you save.')) return;
    var $next = $row.next('.ak-row').length ? $row.next('.ak-row') : $row.prev('.ak-row');
    $row.remove();
    refresh($list);
    ($next.length ? $next.find('.ak-remove') : $list.find('.ak-add')).trigger('focus');
    dirty = true;
  });
  $editor.on('click', '.ak-up, .ak-down', function () {
    var $row = $(this).closest('.ak-row');
    if ($(this).hasClass('ak-up')) $row.insertBefore($row.prev('.ak-row'));
    else $row.insertAfter($row.next('.ak-row'));
    refresh($row.closest('.ak-list'));
    $(this).trigger('focus');
    dirty = true;
  });

  /* Photos: pick from the Media Library. */
  var frame = null, $target = null;
  function setPreview($box, url) {
    $box.find('.ak-image-preview').html(url ? $('<img alt="">').attr('src', url) : '<span>No photo</span>');
  }
  $editor.on('click', '.ak-image-choose', function () {
    $target = $(this).closest('.ak-image');
    if (!frame) {
      frame = wp.media({ title: 'Choose a photo', button: { text: 'Use this photo' }, library: { type: 'image' }, multiple: false });
      frame.on('select', function () {
        var a = frame.state().get('selection').first().toJSON();
        var url = (a.sizes && (a.sizes.medium_large || a.sizes.large || a.sizes.full) || a).url;
        $target.find('.ak-image-id').val(a.id);
        $target.find('.ak-image-remove').val('');
        if (a.alt && !$target.find('input[name$="[alt]"]').val()) $target.find('input[name$="[alt]"]').val(a.alt);
        setPreview($target, url);
        $target.find('.ak-image-clear').removeAttr('hidden');
        if ($target.data('has-default') === 1 || $target.data('has-default') === '1') $target.find('.ak-image-reset').removeAttr('hidden');
        $target.find('.ak-image-choose').text('Replace photo');
        dirty = true;
      });
    }
    frame.open();
  });
  $editor.on('click', '.ak-image-reset', function () {
    var $box = $(this).closest('.ak-image');
    $box.find('.ak-image-id').val(0);
    $box.find('.ak-image-remove').val('');
    setPreview($box, $box.find('input[data-default-url]').data('default-url'));
    $(this).attr('hidden', true);
    dirty = true;
  });
  $editor.on('click', '.ak-image-clear', function () {
    var $box = $(this).closest('.ak-image');
    $box.find('.ak-image-id').val(0);
    $box.find('.ak-image-remove').val('1');
    setPreview($box, '');
    $(this).attr('hidden', true);
    $box.find('.ak-image-reset').attr('hidden', !$box.find('input[data-default-url]').length);
    $box.find('.ak-image-choose').text('Choose photo');
    dirty = true;
  });
})(jQuery);
