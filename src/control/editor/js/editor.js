const SLIDELIST_UPDATE_INTERVAL = 20000;

// Some sane default values for new slides.
const NEW_SLIDE_DEFAULTS = {
	'id': null,
	'name': 'New Slide',
	'time': 5000,
	'markup': '<p></p>',
	'index': 0
};

const SLIDE_SAVE = $("#btn-slide-save");
const SLIDE_REMOVE = $("#btn-slide-remove");
const SLIDE_NAME = $("#slide-name");
const SLIDE_TIME = $("#slide-time");
const SLIDE_INDEX = $("#slide-index");
const EDITOR_STATUS = $("#editor-status");
var SLIDE_INPUT = null;

var _selected_slide = null;

function set_editor_status(str) {
	EDITOR_STATUS.text(str);
}

function clear_editor_controls() {
	SLIDE_INPUT.setValue('');
	SLIDE_NAME.val('');
	SLIDE_TIME.val(1);
	SLIDE_INDEX.val('');
}

function disable_editor_controls() {
	SLIDE_INPUT.setReadOnly(true);
	SLIDE_NAME.prop("disabled", true);
	SLIDE_TIME.prop("disabled", true);
	SLIDE_INDEX.prop("disabled", true);
	SLIDE_SAVE.prop("disabled", true);
	SLIDE_REMOVE.prop("disabled", true);
}

function enable_editor_controls() {
	SLIDE_INPUT.setReadOnly(false);
	SLIDE_NAME.prop("disabled", false);
	SLIDE_TIME.prop("disabled", false);
	SLIDE_INDEX.prop("disabled", false);
	SLIDE_SAVE.prop("disabled", false);
	SLIDE_REMOVE.prop("disabled", false);
}

function slide_show(slide) {
	console.log("LibreSignage: Show slide '" + slide + "'");

	_selected_slide = new Slide();
	_selected_slide.load(slide, function(ret) {
		if (!ret) {
			console.log("LibreSignage: API error!");
			set_editor_status("Failed to load slide!");
			clear_editor_controls();
			disable_editor_controls();
			return;
		}

		SLIDE_INPUT.setValue(_selected_slide.get('markup'));
		SLIDE_INPUT.clearSelection(); // Deselect new text.

		SLIDE_NAME.val(_selected_slide.get('name'));
		SLIDE_TIME.val(_selected_slide.get('time')/1000);
		SLIDE_INDEX.val(_selected_slide.get('index'));
		enable_editor_controls();
	});
}

function slide_rm() {
	if (!_selected_slide) {
		dialog(DIALOG.ALERT, "Please select a slide", "Please select " +
				"a slide to remove first.", null);
		return;
	}
	set_editor_status("Deleting slide...");

	dialog(DIALOG.CONFIRM, "Delete slide?", "Are you sure you want " +
			"to delete slide '" + _selected_slide.get("name") + "'.",
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
				_selected_slide = null;
				slidelist_trigger_update();
				clear_editor_controls();
				disable_editor_controls();
				set_editor_status("Slide deleted!");
			});
		}
	});
}

function slide_new() {
	console.log("LibreSignage: Create slide!");
	set_editor_status("Creating new slide...");

	_selected_slide = new Slide();
	_selected_slide.set(NEW_SLIDE_DEFAULTS);

	SLIDE_INPUT.setValue(_selected_slide.get('markup'));
	SLIDE_INPUT.clearSelection(); // Deselect new text.

	SLIDE_NAME.val(_selected_slide.get('name'));
	SLIDE_TIME.val(_selected_slide.get('time')/1000);
	SLIDE_INDEX.val(_selected_slide.get('index'));
	enable_editor_controls();

	/*
	*  Leave the remove button disabled since the
	*  new slide is not yet saved and can't be removed.
	*/
	SLIDE_REMOVE.prop('disabled', true);

	set_editor_status("Slide created!");
}

function slide_save() {
	console.log("LibreSignage: Save slide");
	set_editor_status("Saving...");

	var ret = _selected_slide.set({
		'name': SLIDE_NAME.val(),
		'time': SLIDE_TIME.val()*1000,
		'index': SLIDE_INDEX.val(),
		'markup': SLIDE_INPUT.getValue()
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

		/*
		*  Make sure the Remove button is enabled. This
		*  is needed because the New button enables all other
		*  editor controls except the Remove button.
		*/
		SLIDE_REMOVE.prop('disabled', false);

		slidelist_trigger_update();
	});
}

function slide_preview() {
	/*
	*  Preview the current slide in a new window.
	*/
	if (_selected_slide) {
		if (_selected_slide.get('id') != "__API_K_NULL__") {
			window.open("/app/?preview=" +
				_selected_slide.get('id'));
		} else {
			dialog(DIALOG.ALERT, "Please save the slide first",
				"Slides can't be previewed before they " +
				"are saved.", null);
		}
	} else {
		dialog(DIALOG.ALERT, "Please select a slide",
			"Please select a slide to preview first.", null);
	}
}


function editor_setup() {
	// Setup the ACE editor with the Dawn theme and plaintext mode.
	SLIDE_INPUT = ace.edit('slide-input');
	SLIDE_INPUT.setTheme('ace/theme/dawn');
	SLIDE_INPUT.$blockScrolling = Infinity;

	// Disable inputs initially and setup update intevals.
	disable_editor_controls();
	slidelist_trigger_update();
	setInterval(slidelist_trigger_update,
		SLIDELIST_UPDATE_INTERVAL);
}

editor_setup();
