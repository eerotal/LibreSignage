const DIALOG_MARKUP_TOO_LONG = (max) => {
	return new Dialog(
		DIALOG.ALERT,
		'Too long slide markup',
		`The slide markup is too long. The maximum length is
		${max} characters.`,
		null
	);
}

const DIALOG_SLIDE_NOT_SAVED = (callback) => {
	return new Dialog(
		DIALOG.CONFIRM,
		'Slide not saved',
		'The selected slide has unsaved changes. All changes ' +
		'will be lost if you continue. Are you sure you want ' +
		'to continue?',
		callback
	);
}

// Some sane default values for new slides.
const NEW_SLIDE_DEFAULTS = {
	'id': null,
	'name': 'NewSlide',
	'owner': null,
	'time': 5000,
	'markup': '',
	'index': 0,
	'enabled': true,
	'sched': false,
	'sched_t_s': Math.round(Date.now()/1000),
	'sched_t_e': Math.round(Date.now()/1000),
	'animation': 0,
	'queue_name': '',
	'collaborators': []
};

const QUEUE_SELECT		= $("#queue-select");
const QUEUE_VIEW		= $("#queue-view");
const QUEUE_REMOVE		= $("#queue-remove");

const SLIDE_PREVIEW             = $("#btn-slide-preview");
const SLIDE_SAVE                = $("#btn-slide-save");
const SLIDE_REMOVE              = $("#btn-slide-remove");
const SLIDE_CH_QUEUE		= $("#btn-slide-ch-queue");
const SLIDE_DUP			= $("#btn-slide-dup");
const SLIDE_CANT_EDIT		= $("#slide-cant-edit");
const SLIDE_EDIT_AS_COLLAB	= $("#slide-edit-as-collab");
const SLIDE_NAME                = $("#slide-name");
const SLIDE_NAME_GRP            = $("#slide-name-group");
const SLIDE_OWNER               = $("#slide-owner");
const SLIDE_TIME                = $("#slide-time");
const SLIDE_TIME_GRP            = $("#slide-time-group");
const SLIDE_INDEX               = $("#slide-index");
const SLIDE_INDEX_GRP           = $("#slide-index-group");
const SLIDE_EN                  = $("#slide-enabled");
const SLIDE_SCHED               = $("#slide-sched");
const SLIDE_SCHED_DATE_S        = $("#slide-sched-date-s");
const SLIDE_SCHED_TIME_S        = $("#slide-sched-time-s");
const SLIDE_SCHED_DATE_E        = $("#slide-sched-date-e");
const SLIDE_SCHED_TIME_E        = $("#slide-sched-time-e");
const SLIDE_ANIMATION           = $("#slide-animation")
var SLIDE_COLLAB		= null;
var SLIDE_INPUT                 = null;

/*
*  Permissions for using the various editor controls. Each control
*  has a 'perm' function for checking whether a user has permissions
*  to use it and a 'state' function for enabling/disabling the control.
*  The arguments to 'perm' are booleans that represent whether the user
*  is the slide owner (o) or a collaborator (c). Note that some
*  scheduling related functions are excluded from this list since
*  they are handled by scheduling_handle_input_enable().
*/
const SLIDE_CTRL_PERMS = {
	'SLIDE_PREVIEW':  {
		'perm': (o, c) => { return true; },
		'state': (s) => { SLIDE_PREVIEW.prop('disabled', !s); }
	},
	'SLIDE_SAVE': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_SAVE.prop('disabled', !s); }
	},
	'SLIDE_REMOVE': {
		'perm': (o, c) => { return o; },
		'state': (s) => { SLIDE_REMOVE.prop('disabled', !s); }
	},
	'SLIDE_CH_QUEUE': {
		'perm': (o, c) => { return o; },
		'state': (s) => { SLIDE_CH_QUEUE.prop('disabled', !s); }
	},
	'SLIDE_DUP': {
		'perm': (o, c) => { return true; },
		'state': (s) => { SLIDE_DUP.prop('disabled', !s); }
	},
	'SLIDE_CANT_EDIT': {
		'perm': (o, c) => { return !o && !c; },
		'state': (s) => {
			SLIDE_CANT_EDIT.css(
				'display',
				s ? 'block': 'none'
			);
		}
	},
	'SLIDE_EDIT_AS_COLLAB': {
		'perm': (o, c) => { return c; },
		'state': (s) => {
			SLIDE_EDIT_AS_COLLAB.css(
				'display',
				s ? 'block': 'none'
			);
		}
	},
	'SLIDE_NAME': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => {
			SLIDE_NAME.prop('disabled', !s);
			if (s) {
				name_sel.enable();
			} else {
				name_sel.disable();
			}
		}
	},
	'SLIDE_OWNER': {
		'perm': (o, c) => { return false; },
		'state': (s) => { SLIDE_OWNER.prop('disabled', !s); }
	},
	'SLIDE_TIME': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_TIME.prop('disabled', !s); }
	},
	'SLIDE_INDEX': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => {
			SLIDE_INDEX.prop('disabled', !s);
			if (s) {
				index_sel.enable();
			} else {
				index_sel.disable();
			}
		}
	},
	'SLIDE_EN': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_EN.prop('disabled', !s); }
	},
	'SLIDE_SCHED': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_SCHED.prop('disabled', !s); }
	},
	'SLIDE_ANIMATION': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_ANIMATION.prop('disabled', !s); }
	},
	'SLIDE_COLLAB': {
		'perm': (o, c) => { return o; },
		'state': (s) => {
			if (s) {
				SLIDE_COLLAB.enable();
			} else {
				SLIDE_COLLAB.disable();
			}
		}
	},
	'SLIDE_INPUT': {
		'perm': (o, c) => { return o || c; },
		'state': (s) => { SLIDE_INPUT.setReadOnly(!s); }
	}
}

var name_sel = null;
var index_sel = null;
var sel_slide = null;

var flag_slide_loading = false; // Used by slide_show().

function set_editor_inputs(slide) {
	/*
	*  Display the data of 'slide' on the editor inputs.
	*/
	if (!slide) {
		SLIDE_INPUT.setValue('');
		SLIDE_NAME.val('');
		SLIDE_OWNER.val('');
		SLIDE_COLLAB.set([]);
		SLIDE_TIME.val('1');
		SLIDE_INDEX.val('');
		SLIDE_EN.prop('checked', false);
		SLIDE_SCHED.prop('checked', false);
		SLIDE_SCHED_DATE_S.val('');
		SLIDE_SCHED_TIME_S.val('');
		SLIDE_SCHED_DATE_E.val('');
		SLIDE_SCHED_TIME_E.val('');
		SLIDE_ANIMATION.val('0');
	} else {
		SLIDE_INPUT.setValue(slide.get('markup'));
		SLIDE_NAME.val(slide.get('name'));
		SLIDE_OWNER.val(slide.get('owner'));
		SLIDE_COLLAB.set(slide.get('collaborators'));
		SLIDE_TIME.val(slide.get('time')/1000);
		SLIDE_INDEX.val(slide.get('index'));
		SLIDE_EN.prop('checked', slide.get('enabled'));
		SLIDE_SCHED.prop('checked', slide.get('sched'));

		var sched_s = tstamp_to_datetime(slide.get('sched_t_s'));
		SLIDE_SCHED_DATE_S.val(sched_s[0]);
		SLIDE_SCHED_TIME_S.val(sched_s[1]);

		var sched_e = tstamp_to_datetime(slide.get('sched_t_e'));
		SLIDE_SCHED_DATE_E.val(sched_e[0]);
		SLIDE_SCHED_TIME_E.val(sched_e[1]);

		SLIDE_ANIMATION.val(slide.get('animation'));
	}
	SLIDE_INPUT.clearSelection(); // Deselect new text.
}

function sel_slide_is_modified() {
	/*
	*  Check whether the selected slide has been modified and
	*  return true in case it has been modified. False is returned
	*  otherwise.
	*/
	var s = sel_slide;
	var tmp = null;

	if (s == null) {
		return false;
	}

	if (SLIDE_INPUT.getValue() != s.get('markup')) {
		return true;
	}
	if (SLIDE_NAME.val() != s.get('name')) {
		return true;
	}
	if (SLIDE_OWNER.val() != s.get('owner')) {
		return true;
	}
	if (parseInt(SLIDE_TIME.val(), 10) != s.get('time')/1000) {
		return true;
	}
	if (SLIDE_INDEX.val() != s.get('index')) {
		return true;
	}
	if (SLIDE_EN.prop('checked') != s.get('enabled')) {
		console.log('b');
		return true;
	}
	if (SLIDE_SCHED.prop('checked') != s.get('sched')) {
		return true;
	}

	tmp = datetime_to_tstamp(
		SLIDE_SCHED_DATE_S.val(),
		SLIDE_SCHED_TIME_S.val()
	);
	if (tmp != s.get('sched_t_s')) {
		return true;
	}

	tmp = datetime_to_tstamp(
		SLIDE_SCHED_DATE_E.val(),
		SLIDE_SCHED_TIME_E.val()
	);
	if (tmp != s.get('sched_t_e')) {
		return true;
	}

	if (parseInt(SLIDE_ANIMATION.val(), 10) != s.get('animation')) {
		return true;
	}

	if (!sets_eq(SLIDE_COLLAB.selected, s.get('collaborators'))) {
		return true;
	}

	return false;
}

function sel_slide_unsaved_confirm(callback) {
	/*
	*  Ask the user whether to continue or not if the selected
	*  slide has unsaved changes. 'callback' is a function that's
	*  called if the user chooses to continue after seeing the
	*  dialog. This function returns true if the dialog is shown
	*  and false otherwise.
	*/
	if (sel_slide_is_modified()) {
		DIALOG_SLIDE_NOT_SAVED((status, val) => {
			if (!status) { return; }
			if (callback) { callback(); }
		}).show();
		return true;
	} else {
		return false;
	}
}

function disable_controls() {
	for (let k of Object.keys(SLIDE_CTRL_PERMS)) {
		SLIDE_CTRL_PERMS[k]['state'](false);
	}
}

function enable_controls() {
	var o = sel_slide.get('owner') == API_CONFIG.user;
	var c = sel_slide.get('collaborators').includes(API_CONFIG.user);
	var cont = null;
	for (let k of Object.keys(SLIDE_CTRL_PERMS)) {
		cont = SLIDE_CTRL_PERMS[k];
		cont['state'](cont['perm'](o, c));
	}
	scheduling_handle_input_enable();
}

function scheduling_handle_input_enable() {
	/*
	*  Enable/disable various inputs based on whether
	*  scheduling is enabled. This function is called
	*  by the onchange event of SLIDE_SCHED and also
	*  by the enable_controls function.
	*/
	if (SLIDE_SCHED.prop('checked')) {
		// Scheduling -> enable checkbox disabled
		SLIDE_EN.prop("disabled", true);

		/*
		*  Make sure the slide enable checkbox has
		*  the correct value even if the user has
		*  changed it. This can be done since the
		*  user can't manually enable slides when
		*  scheduling is enabled.
		*/
		SLIDE_EN.prop('checked', sel_slide.get('enabled'));

		// Enable scheduling inputs.
		SLIDE_SCHED_DATE_S.prop('disabled', false);
		SLIDE_SCHED_TIME_S.prop('disabled', false);
		SLIDE_SCHED_DATE_E.prop('disabled', false);
		SLIDE_SCHED_TIME_E.prop('disabled', false);
	} else {
		// No scheduling -> enable checkbox enabled
		SLIDE_EN.prop("disabled", false);

		// Disable scheduling inputs.
		SLIDE_SCHED_DATE_S.prop('disabled', true);
		SLIDE_SCHED_TIME_S.prop('disabled', true);
		SLIDE_SCHED_DATE_E.prop('disabled', true);
		SLIDE_SCHED_TIME_E.prop('disabled', true);
	}
}

function slide_show(slide, no_popup) {
	/*
	*  Show the slide 'slide'.
	*/
	var cb = function() {
		console.log(`LibreSignage: Show slide '${slide}'.`);

		flag_slide_loading = true;
		sel_slide = new Slide();
		sel_slide.load(slide, (ret) => {
			if (ret) {
				console.log("LibreSignage: API error!");
				set_editor_inputs(null);
				disable_controls();
				return;
			}
			set_editor_inputs(sel_slide);
			enable_controls();
			flag_slide_loading = false;
		});
	}

	if (!flag_slide_loading) {
		if (!no_popup) {
			if (!sel_slide_unsaved_confirm(cb)) { cb(); }
		} else {
			cb();
		}
	}
}

function slide_rm() {
	/*
	*  Remove the selected slide.
	*/
	if (!sel_slide) {
		dialog(DIALOG.ALERT,
			"Please select a slide",
			"Please select a slide to remove first.",
			null
		);
		return;
	}

	dialog(DIALOG.CONFIRM,
		"Delete slide?",
		`Are you sure you want to delete ` +
		`slide '${sel_slide.get("name")}'.`, (status, val) => {
		if (!status) {
			return;
		}
		sel_slide.remove(null, (stat) => {
			if (api_handle_disp_error(stat)) {
				return;
			}

			var id = sel_slide.get('id');
			$(`#slide-btn-${id}`).remove();

			console.log(
				`LibreSignage: Deleted slide ` +
				`'${sel_slide.get('id')}'.`
			);

			sel_slide = null;
			timeline_update()
			set_editor_inputs(null);
			disable_controls();
		});
	});
}

function slide_new() {
	/*
	*  Create a new slide. Note that this function doesn't save
	*  the slide server-side. The user must manually save the
	*  slide afterwards.
	*/
	var cb = () => {
		console.log("LibreSignage: Create slide!");

		sel_slide = new Slide();
		sel_slide.set(NEW_SLIDE_DEFAULTS);

		set_editor_inputs(sel_slide);
		enable_controls();

		/*
		*  Leave the remove button disabled since the
		*  new slide is not saved yet and can't be
		*  removed.
		*/
		SLIDE_REMOVE.prop('disabled', true);
	};

	if (!timeline_queue) {
		dialog(
			DIALOG.ALERT,
			'Please create a queue',
			'You must create a queue before you can ' +
			'add a slide to one.',
			null
		);
		return;
	}
	if (!sel_slide_unsaved_confirm(cb)) { cb(); }
}

function slide_save() {
	/*
	*  Save the currently selected slide.
	*/
	console.log("LibreSignage: Save slide");

	if (SLIDE_INPUT.getValue().length >
		SERVER_LIMITS.SLIDE_MARKUP_MAX_LEN) {

		DIALOG_MARKUP_TOO_LONG(
			SERVER_LIMITS.SLIDE_MARKUP_MAX_LEN
		).show();
		return;
	}

	sel_slide.set({
		'name': SLIDE_NAME.val(),
		'time': parseInt(SLIDE_TIME.val())*1000,
		'index': parseInt(SLIDE_INDEX.val()),
		'markup': SLIDE_INPUT.getValue(),
		'enabled': SLIDE_EN.prop('checked'),
		'sched': SLIDE_SCHED.prop('checked'),
		'sched_t_s': datetime_to_tstamp(
				SLIDE_SCHED_DATE_S.val(),
				SLIDE_SCHED_TIME_S.val()
			),
		'sched_t_e': datetime_to_tstamp(
				SLIDE_SCHED_DATE_E.val(),
				SLIDE_SCHED_TIME_E.val()
			),
		'animation': parseInt(SLIDE_ANIMATION.val(), 10),
		'queue_name': timeline_queue.name,
		'collaborators': SLIDE_COLLAB.selected
	});

	sel_slide.save((stat) => {
		if (api_handle_disp_error(stat)) {
			return;
		}
		console.log(
			`LibreSignage: Saved slide '` +
			`${sel_slide.get("id")}'.`
		);

		/*
		*  Make sure the Remove button is enabled. This
		*  is needed because the New button enables all other
		*  editor controls except the Remove button.
		*/
		SLIDE_REMOVE.prop('disabled', false);
		timeline_update()

		slide_show(sel_slide.get('id'), true);
	});
}

function slide_preview() {
	/*
	*  Preview the current slide in a new window.
	*/
	if (sel_slide && sel_slide.get('id')) {
		if (sel_slide.get('id') != "__API_K_NULL__") {
			window.open(
				`/app/?preview=${sel_slide.get('id')}`
			);
		} else {
			dialog(
				DIALOG.ALERT,
				"Please save the slide first",
				"Slides can't be previewed before " +
				"they are saved.",
				null
			);
		}
	} else {
		dialog(
			DIALOG.ALERT,
			"No slide selected",
			"Please select a slide to preview or " +
			"save the current slide first.",
			null
		);
	}
}

function slide_ch_queue() {
	queue_get_list((qd) => {
		var queues = {};
		qd.sort();
		for (let q of qd) {
			if (q != sel_slide.get('queue_name')) {
				queues[q] = q;
			}
		}
		dialog(
			DIALOG.SELECT,
			'Select queue',
			'Please select a queue to move the slide to.',
			(status, val) => {
				if (!status) { return; }
				sel_slide.set({'queue_name': val});
				var cb = () => {
					sel_slide.save((err) => {
						api_handle_disp_error(
							err
						);
						if (err) { return; }
						sel_slide = null;
						set_editor_inputs(null);
						disable_controls();
						timeline_update();
					});
				}
				if (!sel_slide_unsaved_confirm(cb)) {
					cb();
				}
			},
			queues
		);
	});
}

function slide_dup() {
	/*
	*  Duplicate the selected slide.
	*/
	sel_slide.dup((s) => {
		sel_slide = s;
		set_editor_inputs(s);
		timeline_update();
	});
}

function inputs_setup(ready) {
	/*
	*  Setup all editor inputs and input validators etc.
	*/

	// Setup validators for the name and index inputs.
	name_sel = new ValidatorSelector(
		SLIDE_NAME,
		SLIDE_NAME_GRP,
		[new StrValidator({
			min: 1,
			max: null,
			regex: null
		}, "The name is too short."),
		new StrValidator({
			min: null,
			max: SERVER_LIMITS.SLIDE_NAME_MAX_LEN,
			regex: null
		}, "The name is too long."),
		new StrValidator({
			min: null,
			max: null,
			regex: /^[A-Za-z0-9_-]*$/
		}, "The name contains invalid characters.")]
	);
	index_sel = new ValidatorSelector(
		SLIDE_INDEX,
		SLIDE_INDEX_GRP,
		[new NumValidator({
			min: 0,
			max: null,
			nan: true,
			float: true
		}, "The index is too small."),
		new NumValidator({
			min: null,
			max: SERVER_LIMITS.SLIDE_MAX_INDEX,
			nan: true,
			float: true
		}, "The index is too large."),
		new NumValidator({
			min: null,
			max: null,
			nan: false,
			float: false
		}, "The index must be an integer value.")]
	);

	val_trigger = new ValidatorTrigger(
		[name_sel, index_sel],
		(valid) => {
			SLIDE_SAVE.prop('disabled', !valid);
		}
	);

	/*
	*  Handle enabling/disabling editor inputs when
	*  scheduling is enabled/disabled.
	*/
	SLIDE_SCHED.change(scheduling_handle_input_enable);

	// Setup the ACE editor with the Dawn theme + plaintext mode.
	SLIDE_INPUT = ace.edit('slide-input');
	SLIDE_INPUT.setTheme('ace/theme/dawn');
	SLIDE_INPUT.$blockScrolling = Infinity;

	// Setup the collaborators multiselector w/ validators.
	api_call(API_ENDP.USERS_LIST, {}, (data) => {
		if (api_handle_disp_error(data['error'])) { return; }

		SLIDE_COLLAB = new MultiSelect(
			'slide-collab',
			[new WhitelistValidator(
				{ wl: data['users'] },
				"This user doesn't exist."
			),
			new BlacklistValidator(
				{ bl: [API_CONFIG.user] },
				"You can't add yourself " +
				"as a collaborator."
			)]
		);
		if (ready) { ready(); }
	});
}

function editor_setup() {
	setup_defaults();

	/*
	*  Add a listener for the 'beforeunload' event to make sure
	*  the user doesn't accidentally exit the page and lose changes.
	*/
	$(window).on('beforeunload', function(e) {
		if (!sel_slide_is_modified()) {
			return;
		}

		e.returnValue = "The selected slide is not saved. " +
				"Any changes will be lost if you exit " +
				"the page. Are you sure you want to " +
				"continue?";
		return e.returnValue;
	});

	inputs_setup(() => {
		// Disable inputs and setup update intervals.
		disable_controls();
		queue_setup();
	});
}

$(document).ready(() => {
	api_init(
		null,	// Use default config.
		editor_setup
	);
});
