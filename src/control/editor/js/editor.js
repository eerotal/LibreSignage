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
const QUEUE_CREATE		= $("#queue-create");
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
*  Editor UI definitions using the UIControl class.
*/
const UI_DEFS= {
	'SLIDE_PREVIEW': new UIControl(
		_elem = SLIDE_PREVIEW,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_SAVE': new UIControl(
		_elem = SLIDE_SAVE,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_REMOVE': new UIControl(
		_elem = SLIDE_REMOVE,
		_perm = (d) => { return d['o']; },
		_enabler = (elem, s) => {elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_CH_QUEUE': new UIControl(
		_elem = SLIDE_CH_QUEUE,
		_perm = (d) => { return d['o']; },
		_enabler = (elem, s) => {elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_DUP': new UIControl(
		_elem = SLIDE_DUP,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => {elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_CANT_EDIT': new UIControl(
		_elem = SLIDE_CANT_EDIT,
		_perm = (d) => { return !d['o'] && !d['c']; },
		_enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_EDIT_AS_COLLAB': new UIControl(
		_elem = SLIDE_EDIT_AS_COLLAB,
		_perm = (d) => {
			return d['c'] && sel_slide != null;
		},
		_enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_NAME': new UIControl(
		_elem = SLIDE_NAME,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, data) => {
			return elem.val() != data.get('name');
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, slide) => {
			elem.val(slide.get('name'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_OWNER': new UIControl(
		_elem = SLIDE_OWNER,
		_perm = null,
		_enabler = null,
		_mod = null,
		_getter = null,
		_setter = (elem, slide) => {
			elem.val(slide.get('owner'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_TIME': new UIControl(
		_elem = SLIDE_TIME,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var time = parseInt(elem.val(), 10);
			return time != slide.get('time')/1000;
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, slide) => {
			var time = parseInt(slide.get('time'), 10);
			elem.val(time/1000);
		},
		_clear = (elem) => { elem.val(1); }
	),
	'SLIDE_INDEX': new UIControl(
		_elem = SLIDE_INDEX,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var tmp = parseInt(elem.val(), 10);
			return tmp != slide.get('index');
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, slide) => {
			elem.val(slide.get('index'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_EN': new UIControl(
		_elem = SLIDE_EN,
		_perm = (d) => {
			if (!UI_DEFS['SLIDE_SCHED'].get()) {
				return d['o'] || d['c'];
			} else {
				return false;
			}
		},
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			return elem.prop('checked')
				!= slide.get('enabled');
		},
		_getter = (elem) => { return elem.prop('checked'); },
		_setter = (elem, slide) => {
			elem.prop('checked', slide.get('enabled'));
		},
		_clear = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED': new UIControl(
		_elem = SLIDE_SCHED,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			return elem.prop('checked')
				!= slide.get('sched');
		},
		_getter = (elem) => { return elem.prop('checked'); },
		_setter = (elem, slide) => {
			elem.prop('checked', slide.get('sched'));
		},
		_clear = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED_DATE_S': new UIControl(
		_elem = SLIDE_SCHED_DATE_S,
		_perm = (d) => {
			return UI_DEFS['SLIDE_SCHED'].get()
				&& (d['o'] || d['c']);
		},
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_s')
			)[0];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_s')
			)[0];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_S': new UIControl(
		_elem = SLIDE_SCHED_TIME_S,
		_perm = (d) => {
			return UI_DEFS['SLIDE_SCHED'].get()
				&& (d['o'] || d['c']);
		},
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_s')
			)[1];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_s')
			)[1];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_DATE_E': new UIControl(
		_elem = SLIDE_SCHED_DATE_E,
		_perm = (d) => {
			return UI_DEFS['SLIDE_SCHED'].get()
				&& (d['o'] || d['c']);
		},
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_e')
			)[0];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_e')
			)[0];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_E': new UIControl(
		_elem = SLIDE_SCHED_TIME_E,
		_perm = (d) => {
			return UI_DEFS['SLIDE_SCHED'].get()
				&& (d['o'] || d['c']);
		},
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_e')
			)[1];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, slide) => {
			var tmp = tstamp_to_datetime(
				slide.get('sched_t_e')
			)[1];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_ANIMATION': new UIControl(
		_elem = SLIDE_ANIMATION,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = (elem, slide) => {
			var anim = parseInt(elem.val(), 10);
			return anim != slide.get('animation');
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, slide) => {
			elem.val(slide.get('animation'));
		},
		_clear = (elem) => { elem.val(0); }
	),
	'SLIDE_COLLAB': new UIControl(
		_elem = () => { return SLIDE_COLLAB; },
		_perm = (d) => { return d['o']; },
		_enabler = (elem, s) => {
			if (s) {
				elem.enable();
			} else {
				elem.disable();
			}
		},
		_mod = (elem, slide) => {
			return !sets_eq(
				elem.selected,
				slide.get('collaborators')
			);
		},
		_getter = (elem) => { return elem.selected; },
		_setter = (elem, slide) => {
			elem.set(slide.get('collaborators'));
		},
		_clear = (elem) => { elem.set([]); }
	),
	'SLIDE_INPUT': new UIControl(
		_elem = () => { return SLIDE_INPUT; },
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.setReadOnly(!s); },
		_mod = (elem, slide) => {
			return elem.getValue() != slide.get('markup');
		},
		_getter = (elem) => { return elem.getValue(); },
		_setter = (elem, slide) => {
			elem.setValue(slide.get('markup'));
			SLIDE_INPUT.clearSelection();
		},
		_clear = (elem) => {
			elem.setValue('');
			SLIDE_INPUT.clearSelection();
		}
	)
}

var name_sel = null;
var index_sel = null;
var sel_slide = null;

var flag_slide_loading = false; // Used by slide_show().

function disable_controls() {
	name_sel.disable();
	index_sel.disable();
	for (let k of Object.keys(UI_DEFS)) {
		UI_DEFS[k].set_state(false);
	}
}

function enable_controls() {
	var o = (
		!sel_slide.get('owner') // New slide.
		|| sel_slide.get('owner') == API_CONFIG.user
	);
	var c = (
		sel_slide.get('collaborators').includes(API_CONFIG.user)
	);
	for (let k of Object.keys(UI_DEFS)) {
		UI_DEFS[k].state({'o': o, 'c': c});
	}
	name_sel.enable();
	index_sel.disable();
}

function set_inputs(slide) {
	/*
	*  Display the data of 'slide' on the editor inputs.
	*/
	if (!slide) {
		for (let key of Object.keys(UI_DEFS)) {
			UI_DEFS[key].clear();
		}
	} else {
		for (let key of Object.keys(UI_DEFS)) {
			UI_DEFS[key].set(slide);
		}
	}
}

function sel_slide_is_modified() {
	if (!sel_slide) { return false; }
	for (let key of Object.keys(UI_DEFS)) {
		if (UI_DEFS[key].is_mod(sel_slide)) {
			return true;
		}
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
				set_inputs(null);
				disable_controls();
				return;
			}
			set_inputs(sel_slide);
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
			set_inputs(null);
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

		set_inputs(sel_slide);
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
		'name': UI_DEFS['SLIDE_NAME'].get(),
		'time': UI_DEFS['SLIDE_TIME'].get()*1000,
		'index': UI_DEFS['SLIDE_INDEX'].get(),
		'markup': UI_DEFS['SLIDE_INPUT'].get(),
		'enabled': UI_DEFS['SLIDE_EN'].get(),
		'sched': UI_DEFS['SLIDE_SCHED'].get(),
		'sched_t_s': datetime_to_tstamp(
				UI_DEFS['SLIDE_SCHED_DATE_S'].get(),
				UI_DEFS['SLIDE_SCHED_TIME_S'].get()
			),
		'sched_t_e': datetime_to_tstamp(
				UI_DEFS['SLIDE_SCHED_DATE_E'].get(),
				UI_DEFS['SLIDE_SCHED_TIME_E'].get()
			),
		'animation': UI_DEFS['SLIDE_ANIMATION'].get(),
		'queue_name': timeline_queue.name,
		'collaborators': UI_DEFS['SLIDE_COLLAB'].get()
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
						set_inputs(null);
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
		set_inputs(s);
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
	*  Update enabled controls when scheduling is enabled.
	*/
	SLIDE_SCHED.change(enable_controls);

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
