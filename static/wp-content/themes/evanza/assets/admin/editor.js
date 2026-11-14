/* Admin editing for products and categories. Plain jQuery, no build step. */
(function ($) {
	'use strict';

	function mediaFrame(options, onSelect) {
		var frame = wp.media(options);
		frame.on('select', function () {
			onSelect(frame.state().get('selection'));
		});
		frame.open();
	}

	// ---- Product colour rows ----
	var $list = $('#evz-variants');
	if ($list.length) {
		var template = $('#evz-variant-template').html();

		function initRow($row) {
			$row.find('.evz-color').wpColorPicker();
			$row.find('.evz-photos').sortable({ update: function () { syncPhotos($row); } });
		}
		function syncPhotos($row) {
			var items = $row.find('.evz-photos li').map(function () {
				var v = $(this).data('value');
				return v;
			}).get();
			$row.find('.evz-photos-input').val(JSON.stringify(items));
		}

		$list.children().each(function () { initRow($(this)); });
		$list.sortable({ handle: '.evz-handle', items: '> .evz-variant', axis: 'y', tolerance: 'pointer' });

		$('#evz-add-variant').on('click', function () {
			var key = 'n' + Date.now();
			var $row = $(template.replace(/__i__/g, key));
			$list.append($row);
			initRow($row);
			$row.find('.evz-name').trigger('focus');
		});

		$list.on('click', '.evz-remove', function () {
			if (window.confirm('Remove this colour?')) { $(this).closest('.evz-variant').remove(); }
		});
		$list.on('input', '.evz-name', function () {
			$(this).closest('.evz-variant').find('.evz-variant__title').text($(this).val() || 'New colour');
		});

		// Photos: several at once from the Media Library
		$list.on('click', '.evz-add-photos', function () {
			var $row = $(this).closest('.evz-variant');
			mediaFrame({ title: 'Choose photos', multiple: 'add', library: { type: 'image' }, button: { text: 'Use these photos' } }, function (selection) {
				selection.each(function (att) {
					var a = att.toJSON();
					var thumb = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
					$('<li>').attr('data-value', a.id).data('value', a.id)
						.append($('<img>').attr({ src: thumb, alt: '' }))
						.append($('<button type="button" class="evz-photo-remove" aria-label="Remove photo">&times;</button>'))
						.appendTo($row.find('.evz-photos'));
				});
				$row.find('.evz-photos').sortable('refresh');
				syncPhotos($row);
			});
		});
		$list.on('click', '.evz-photo-remove', function () {
			var $row = $(this).closest('.evz-variant');
			$(this).closest('li').remove();
			syncPhotos($row);
		});

		// Tile picture: one image
		$list.on('click', '.evz-choose-tile', function () {
			var $row = $(this).closest('.evz-variant');
			mediaFrame({ title: 'Choose a tile picture', multiple: false, library: { type: 'image' }, button: { text: 'Use this picture' } }, function (selection) {
				var a = selection.first().toJSON();
				$row.find('.evz-tile-input').val(a.id);
				$row.find('.evz-tile__preview').empty().append($('<img>').attr({ src: a.url, alt: '' }));
			});
		});
		$list.on('click', '.evz-clear-tile', function () {
			var $row = $(this).closest('.evz-variant');
			$row.find('.evz-tile-input').val('');
			$row.find('.evz-tile__preview').empty();
		});
	}

	// ---- Category files (PDF and image) ----
	$(document).on('click', '.evz-file__choose', function () {
		var $box = $(this).closest('.evz-file');
		var pdf = $box.data('type') === 'pdf';
		mediaFrame({ title: pdf ? 'Choose a PDF' : 'Choose an image', multiple: false, library: { type: pdf ? 'application/pdf' : 'image' }, button: { text: 'Use this file' } }, function (selection) {
			$box.find('.evz-file__url').val(selection.first().toJSON().url);
		});
	});
	$(document).on('click', '.evz-file__clear', function () {
		$(this).closest('.evz-file').find('.evz-file__url').val('');
	});
	// The "Add category" form posts by Ajax and keeps its fields: clear ours after it saves.
	$(document).ajaxSuccess(function (event, xhr, settings) {
		if (settings.data && String(settings.data).indexOf('action=add-tag') !== -1) { $('.evz-file__url').val(''); }
	});
})(jQuery);
