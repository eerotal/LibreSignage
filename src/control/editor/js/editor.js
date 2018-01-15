
// Some sane default values for new slides.
var NEW_SLIDE_DEFAULTS = {
	'id': '',
	'time': 5000,
	'markup': '<p></p>',
	'index': 0
};

var SLIDE_INPUT = $("#slide-input");
var SLIDE_NAME = $("#slide-name");
var SLIDE_TIME = $("#slide-time");
var SLIDE_INDEX = $("#slide-index");
var EDITOR_STATUS = $("#editor-status");

var _selected_slide = new Slide();

function set_editor_status(str) {
	EDITOR_STATUS.text(str);
}

function clear_editor_controls() {
	SLIDE_INPUT.val('');
	SLIDE_INPUT.prop("disabled", true);

	SLIDE_NAME.val('');
	SLIDE_NAME.prop("disabled", true);

	SLIDE_TIME.val(1);
	SLIDE_TIME.prop("disabled", true);

	SLIDE_INDEX.val('');
	SLIDE_INDEX.prop("disabled", true);
}

function slide_show(slide) {
	console.log("LibreSignage: Show slide '" + slide + "'");

	_selected_slide.load(slide, function(ret) {
		if (!ret) {
			console.log("LibreSignage: API error!");
			set_editor_status("Failed to load slide!");
			clear_editor_controls();
			return;
		}

		SLIDE_INPUT.val(_selected_slide.get('markup'));
		SLIDE_INPUT.prop("disabled", false);

		SLIDE_NAME.val(_selected_slide.get('id'));
		SLIDE_NAME.prop("disabled", false);

		SLIDE_TIME.val(_selected_slide.get('time')/1000);
		SLIDE_TIME.prop("disabled", false);

		SLIDE_INDEX.val(_selected_slide.get('index'));
		SLIDE_INDEX.prop("disabled", false);
	});
}

function slide_rm() {
	if (!_selected_slide) {
		dialog(DIALOG.ALERT, "Please select a slide", "Please select " +
				"a slide to remove first.", null)
		return;
	}
	set_editor_status("Deleting slide...");

	dialog(DIALOG.CONFIRM, "Delete slide?", "Are you sure you want " +
			"to delete slide '" + _selected_slide.get("id") + "'.",
			function(status, val) {
		if (status) {
			_selected_slide.remove(null, function(ret) {
				if (!ret) {
					console.log("LibreSignage: API error!");
					set_editor_status("Failed to remove slide!");
					return;
				}

				$('#slide-btn-' + _selected_slide.get('id')).remove();

				console.log("LibreSignage: Deleted slide '" +
						_selected_slide.get('id') + "'.");
				_selected_slide.clear();
				clear_editor_controls();
				set_editor_status("Slide deleted!");
			});
		}
	});
}

function slide_new() {
	console.log("LibreSignage: Create slide!");
	set_editor_status("Creating new slide...");

	_selected_slide.clear();
	_selected_slide.set(NEW_SLIDE_DEFAULTS);

	SLIDE_INPUT.val(_selected_slide.get('markup'));
	SLIDE_INPUT.prop("disabled", false);

	SLIDE_NAME.val(_selected_slide.get('id'));
	SLIDE_NAME.prop("disabled", false);

	SLIDE_TIME.val(_selected_slide.get('time')/1000);
	SLIDE_TIME.prop("disabled", false);

	SLIDE_INDEX.val(_selected_slide.get('index'));
	SLIDE_INDEX.prop("disabled", false);

	set_editor_status("Slide created!");
}

function slide_save() {
	console.log("LibreSignage: Save slide");
	set_editor_status("Saving...");

	var ret = _selected_slide.set({
		'id': SLIDE_NAME.val(),
		'time': SLIDE_TIME.val()*1000,
		'index': SLIDE_INDEX.val(),
		'markup': SLIDE_INPUT.val()
	});

	if (!ret) {
		console.log("LibreSignage: Slide data error!");
		set_editor_status("Save failed!");
		return;
	}

	_selected_slide.save(function(ret) {
		if (!ret) {
			console.log("LibreSignage: API error!");
			set_editor_status("Save failed!");
			return;
		}
		console.log("LibreSignage: Saved slide '" + _selected_slide.get("id") + "'.");
		set_editor_status("Saved!");
	});
}
