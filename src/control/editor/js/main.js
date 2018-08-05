var $ = require('jquery');
var bootstrap = require('bootstrap');

var util = require('ls-util');
var val = require('ls-validator');
var dialog = require('ls-dialog');
var api = require('ls-api');
var multiselect = require('ls-multiselect');
var uic = require('ls-uicontrol');
var slide = require('ls-slide');
var queue = require('ls-queue');
var sc = require('ls-shortcut');

var timeline = require('./timeline.js');
var qsel = require('./qsel.js');
var preview = require('./preview.js');

var API = null;
var TL = null;

const DIALOG_MARKUP_TOO_LONG = (max) => {
	return new dialog.Dialog(
		dialog.TYPE.ALERT,
		'Too long slide markup',
		`The slide markup is too long. The maximum length is
		${max} characters.`,
		null
	);
}

const DIALOG_SLIDE_NOT_SAVED = (callback) => {
	return new dialog.Dialog(
		dialog.TYPE.CONFIRM,
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

const SLIDE_NEW					= $("#btn-slide-new")
const SLIDE_PREVIEW				= $("#btn-slide-preview");
const SLIDE_SAVE				= $("#btn-slide-save");
const SLIDE_REMOVE				= $("#btn-slide-remove");
const SLIDE_CH_QUEUE			= $("#btn-slide-ch-queue");
const SLIDE_DUP					= $("#btn-slide-dup");
const SLIDE_CANT_EDIT			= $("#slide-cant-edit");
const SLIDE_EDIT_AS_COLLAB		= $("#slide-edit-as-collab");
const SLIDE_NAME				= $("#slide-name");
const SLIDE_NAME_GRP			= $("#slide-name-group");
const SLIDE_OWNER				= $("#slide-owner");
const SLIDE_TIME				= $("#slide-time");
const SLIDE_TIME_GRP			= $("#slide-time-group");
const SLIDE_INDEX				= $("#slide-index");
const SLIDE_INDEX_GRP			= $("#slide-index-group");
const SLIDE_EN					= $("#slide-enabled");
const SLIDE_SCHED				= $("#slide-sched");
const SLIDE_SCHED_DATE_S		= $("#slide-sched-date-s");
const SLIDE_SCHED_TIME_S		= $("#slide-sched-time-s");
const SLIDE_SCHED_DATE_E		= $("#slide-sched-date-e");
const SLIDE_SCHED_TIME_E		= $("#slide-sched-time-e");
const SLIDE_ANIMATION			= $("#slide-animation")
const PREVIEW_R_16x9			= $("#btn-preview-ratio-16x9");
const PREVIEW_R_4x3				= $("#btn-preview-ratio-4x3");
const MARKUP_ERR_DISPLAY		= $("#markup-err-display");
var SLIDE_COLLAB				= null;
var SLIDE_INPUT					= null;
var LIVE_PREVIEW				= null;

var name_sel = null;
var index_sel = null;
var sel_slide = null;

var flag_slide_loading = false; // Used by slide_show().
var flag_editor_ready = false;

var defer_editor_ready = () => { return !flag_editor_ready; };

/*
*  Editor UI definitions using the UIInput class.
*/
const UI_DEFS = new uic.UIController({
	'PREVIEW_R_16x9': new uic.UIButton(
		_elem = PREVIEW_R_16x9,
		_perm = (d) => {
			return LIVE_PREVIEW.ratio != '16x9';
		},
		_enabler = null,
		_attach = {
			'click': (e) => {
				UI_DEFS.get('PREVIEW_R_16x9').enabled(false);
				UI_DEFS.get('PREVIEW_R_4x3').enabled(true);
				LIVE_PREVIEW.set_ratio('16x9');
			}
		},
		_defer = defer_editor_ready
	),
	'PREVIEW_R_4x3': new uic.UIButton(
		_elem = PREVIEW_R_4x3,
		_perm = (d) => {
			return LIVE_PREVIEW.ratio != '4x3';
		},
		_enabler = null,
		_attach = {
			'click': () => {
				UI_DEFS.get('PREVIEW_R_16x9').enabled(true);
				UI_DEFS.get('PREVIEW_R_4x3').enabled(false);
				LIVE_PREVIEW.set_ratio('4x3');
			}
		},
		_defer = defer_editor_ready
	),
	'SLIDE_NEW': new uic.UIButton(
		_elem = SLIDE_NEW,
		_perm = (d) => { return true; },
		_enabler = null,
		_attach = {
			'click': slide_new
		},
		_defer = defer_editor_ready
	),
	'SLIDE_PREVIEW': new uic.UIButton(
		_elem = SLIDE_PREVIEW,
		_perm = (d) => { return true; },
		_enabler = null,
		_attach = {
			'click': slide_preview
		},
		_defer = defer_editor_ready
	),
	'SLIDE_SAVE': new uic.UIButton(
		_elem = SLIDE_SAVE,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = {
			'click': slide_save
		},
		_defer = defer_editor_ready
	),
	'SLIDE_REMOVE': new uic.UIButton(
		_elem = SLIDE_REMOVE,
		_perm = (d) => {
			return d['o'] && sel_slide.get('id') != null;
		},
		_enabler = null,
		_attach = {
			'click': slide_rm
		},
		_defer = defer_editor_ready
	),
	'SLIDE_CH_QUEUE': new uic.UIButton (
		_elem = SLIDE_CH_QUEUE,
		_perm = (d) => { return d['o']; },
		_enabler = null,
		_attach = {
			'click': slide_ch_queue
		},
		_defer = defer_editor_ready
	),
	'SLIDE_DUP': new uic.UIButton(
		_elem = SLIDE_DUP,
		_perm = (d) => { return true; },
		_enabler = null,
		_attach = {
			'click': slide_dup
		},
		_defer = defer_editor_ready
	),
	'SLIDE_CANT_EDIT': new uic.UIInput(
		_elem = SLIDE_CANT_EDIT,
		_perm = (d) => { return !d['o'] && !d['c']; },
		_enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		_attach = null,
		_defer = null,
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_EDIT_AS_COLLAB': new uic.UIInput(
		_elem = SLIDE_EDIT_AS_COLLAB,
		_perm = (d) => {
			return d['c'] && sel_slide != null;
		},
		_enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		_attach = null,
		_defer = null,
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'SLIDE_NAME': new uic.UIInput(
		_elem = SLIDE_NAME,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, data) => {
			return elem.val() != data.get('name');
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, s) => {
			elem.val(s.get('name'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_OWNER': new uic.UIInput(
		_elem = SLIDE_OWNER,
		_perm = null,
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = null,
		_getter = null,
		_setter = (elem, s) => {
			elem.val(s.get('owner'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_TIME': new uic.UIInput(
		_elem = SLIDE_TIME,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var time = parseInt(elem.val(), 10);
			return time != s.get('time')/1000;
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, s) => {
			var time = parseInt(s.get('time'), 10);
			elem.val(time/1000);
		},
		_clear = (elem) => { elem.val(1); }
	),
	'SLIDE_INDEX': new uic.UIInput(
		_elem = SLIDE_INDEX,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var tmp = parseInt(elem.val(), 10);
			return tmp != s.get('index');
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, s) => {
			elem.val(s.get('index'));
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_EN': new uic.UIInput(
		_elem = SLIDE_EN,
		_perm = (d) => {
			if (!UI_DEFS.get('SLIDE_SCHED').get()) {
				return d['o'] || d['c'];
			} else {
				return false;
			}
		},
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			return elem.prop('checked') != s.get('enabled');
		},
		_getter = (elem) => { return elem.prop('checked'); },
		_setter = (elem, s) => {
			elem.prop('checked', s.get('enabled'));
		},
		_clear = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED': new uic.UIInput(
		_elem = SLIDE_SCHED,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			return elem.prop('checked') != s.get('sched');
		},
		_getter = (elem) => { return elem.prop('checked'); },
		_setter = (elem, s) => {
			elem.prop('checked', s.get('sched'));
		},
		_clear = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED_DATE_S': new uic.UIInput(
		_elem = SLIDE_SCHED_DATE_S,
		_perm = (d) => {
			return UI_DEFS.get('SLIDE_SCHED').get()
				&& (d['o'] || d['c']);
		},
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[0];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[0];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_S': new uic.UIInput(
		_elem = SLIDE_SCHED_TIME_S,
		_perm = (d) => {
			return UI_DEFS.get('SLIDE_SCHED').get()
				&& (d['o'] || d['c']);
		},
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[1];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[1];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_DATE_E': new uic.UIInput(
		_elem = SLIDE_SCHED_DATE_E,
		_perm = (d) => {
			return UI_DEFS.get('SLIDE_SCHED').get()
				&& (d['o'] || d['c']);
		},
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[0];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[0];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_E': new uic.UIInput(
		_elem = SLIDE_SCHED_TIME_E,
		_perm = (d) => {
			return UI_DEFS.get('SLIDE_SCHED').get()
				&& (d['o'] || d['c']);
		},
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[1];
			return elem.val() != tmp;
		},
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[1];
			elem.val(tmp);
		},
		_clear = (elem) => { elem.val(''); }
	),
	'SLIDE_ANIMATION': new uic.UIInput(
		_elem = SLIDE_ANIMATION,
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = null,
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			var anim = parseInt(elem.val(), 10);
			return anim != s.get('animation');
		},
		_getter = (elem) => { return parseInt(elem.val(), 10); },
		_setter = (elem, s) => {
			elem.val(s.get('animation'));
		},
		_clear = (elem) => { elem.val(0); }
	),
	'SLIDE_COLLAB': new uic.UIInput(
		_elem = () => { return SLIDE_COLLAB; },
		_perm = (d) => { return d['o']; },
		_enabler = (elem, s) => {
			if (s) {
				elem.enable();
			} else {
				elem.disable();
			}
		},
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			return !util.sets_eq(
				elem.selected,
				s.get('collaborators')
			);
		},
		_getter = (elem) => { return elem.selected; },
		_setter = (elem, s) => {
			elem.set(s.get('collaborators'));
		},
		_clear = (elem) => { elem.set([]); }
	),
	'SLIDE_INPUT': new uic.UIInput(
		_elem = () => { return SLIDE_INPUT; },
		_perm = (d) => { return d['o'] || d['c']; },
		_enabler = (elem, s) => { elem.setReadOnly(!s); },
		_attach = null,
		_defer = null,
		_mod = (elem, s) => {
			return elem.getValue() != s.get('markup');
		},
		_getter = (elem) => { return elem.getValue(); },
		_setter = (elem, s) => {
			elem.setValue(s.get('markup'));
			SLIDE_INPUT.clearSelection();
		},
		_clear = (elem) => {
			elem.setValue('');
			SLIDE_INPUT.clearSelection();
		}
	)
});

/*
*  Editor shortcut definitions. Note that the shortcut
*  callbacks check whether the action should be performed
*  by checking whether the corresponding button is enabled.
*/
var EDITOR_SHORTCUTS = new sc.ShortcutController([
	new sc.Shortcut( // Ctrl+Alt+n => New slide
		keys = ['Control', 'Alt', 'n'],
		func = () => {
			if (!SLIDE_NEW.prop('disabled')) {
				slide_new();
			}
		},
		defer = defer_editor_ready
	),
	new sc.Shortcut( // Ctrl+s => Save
		keys = ['Control', 's'],
		func = () => {
			if (!SLIDE_SAVE.prop('disabled')) {
				slide_save();
			}
		},
		defer = defer_editor_ready
	),
	new sc.Shortcut( // Ctrl+d => Duplicate
		keys = ['Control', 'd'],
		func = () => {
			if (!SLIDE_DUP.prop('disabled')) {
				slide_dup();
			}
		},
		defer = defer_editor_ready
	),
	new sc.Shortcut( // Ctrl+p => Preview
		keys = ['Control', 'p'],
		func = () => {
			if (!SLIDE_PREVIEW.prop('disabled')) {
				slide_preview();
			}
		},
		defer = defer_editor_ready
	),
	new sc.Shortcut( // Ctrl+q => Change queue
		keys = ['Control', 'q'],
		func = () => {
			if (!SLIDE_CH_QUEUE.prop('disabled')) {
				slide_ch_queue();
			}
		},
		defer = defer_editor_ready
	)
]);

function disable_controls() {
	name_sel.disable();
	index_sel.disable();
	UI_DEFS.all(function() { this.enabled(false); });
	UI_DEFS.get('SLIDE_NEW').enabled(true);
}

function enable_controls() {
	var o = (
		!sel_slide.get('owner') // New slide.
		|| sel_slide.get('owner') == API.CONFIG.user
	);
	var c = (
		sel_slide.get('collaborators').includes(API.CONFIG.user)
	);
	UI_DEFS.all(function(d) { this.state(d); }, {'o': o, 'c': c});
	name_sel.enable();
	index_sel.disable();
}

function set_inputs(s) {
	/*
	*  Display the data of 'slide' on the editor inputs.
	*/
	if (!s) {
		UI_DEFS.all(function() { this.clear(); }, null, 'input');
	} else {
		UI_DEFS.all(function(data) { this.set(data); }, s, 'input');
	}
}

function sel_slide_is_modified() {
	var ret = false;
	if (!sel_slide) { return false; }
	UI_DEFS.all(
		function() {
			if (this.is_mod(sel_slide)) {
				ret = true;
				return false;
			}
		},
		null,
		'input'
	);
	return ret;
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

function slide_show(s, no_popup) {
	/*
	*  Show the slide 's'.
	*/
	var cb = () => {
		console.log(`LibreSignage: Show slide '${s}'.`);

		sel_slide = new slide.Slide(API);
		flag_slide_loading = true;
		sel_slide.load(s, (ret) => {
			if (ret) {
				set_inputs(null);
				disable_controls();
				LIVE_PREVIEW.update();
				return;
			}
			set_inputs(sel_slide);
			enable_controls();
			LIVE_PREVIEW.update();
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
	dialog.dialog(
		dialog.TYPE.CONFIRM,
		"Delete slide?",
		`Are you sure you want to delete ` +
		`slide '${sel_slide.get("name")}'.`,
		(status, val) => {
			if (!status) { return; }
			sel_slide.remove(null, (stat) => {
				if (API.handle_disp_error(stat)) { return; }
				$(`#slide-btn-${sel_slide.get('id')}`).remove();
				console.log(
					`LibreSignage: Deleted slide ` +
					`'${sel_slide.get('id')}'.`
				);

				sel_slide = null;
				TL.update()
				set_inputs(null);
				disable_controls();
				LIVE_PREVIEW.update();
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
		sel_slide = new slide.Slide(API);
		sel_slide.set(NEW_SLIDE_DEFAULTS);
		set_inputs(sel_slide);
		enable_controls();
	};

	if (!TL.queue) {
		dialog.dialog(
			dialog.TYPE.ALERT,
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

	if (
		UI_DEFS.get('SLIDE_INPUT').get().length >
		API.SERVER_LIMITS.SLIDE_MARKUP_MAX_LEN
	) {
		DIALOG_MARKUP_TOO_LONG(
			API.SERVER_LIMITS.SLIDE_MARKUP_MAX_LEN
		).show();
		return;
	}

	sel_slide.set({
		'name': UI_DEFS.get('SLIDE_NAME').get(),
		'time': UI_DEFS.get('SLIDE_TIME').get()*1000,
		'index': UI_DEFS.get('SLIDE_INDEX').get(),
		'markup': UI_DEFS.get('SLIDE_INPUT').get(),
		'enabled': UI_DEFS.get('SLIDE_EN').get(),
		'sched': UI_DEFS.get('SLIDE_SCHED').get(),
		'sched_t_s': util.datetime_to_tstamp(
				UI_DEFS.get('SLIDE_SCHED_DATE_S').get(),
				UI_DEFS.get('SLIDE_SCHED_TIME_S').get()
			),
		'sched_t_e': util.datetime_to_tstamp(
				UI_DEFS.get('SLIDE_SCHED_DATE_E').get(),
				UI_DEFS.get('SLIDE_SCHED_TIME_E').get()
			),
		'animation': UI_DEFS.get('SLIDE_ANIMATION').get(),
		'queue_name': TL.queue.name,
		'collaborators': UI_DEFS.get('SLIDE_COLLAB').get()
	});

	sel_slide.save((stat) => {
		if (API.handle_disp_error(stat)) {
			return;
		}
		console.log(
			`LibreSignage: Saved slide '${sel_slide.get("id")}'.`
		);

		// Enable all controls now that the slide is saved.
		enable_controls();

		TL.update();
		slide_show(sel_slide.get('id'), true);
	});
}

function slide_preview() {
	// Preview the current slide in a new window.
	window.open(`/app/?preview=${sel_slide.get('id')}`);
}

function slide_ch_queue() {
	queue.get_list(API, (qd) => {
		var queues = {};
		qd.sort();
		for (let q of qd) {
			if (q != sel_slide.get('queue_name')) { queues[q] = q; }
		}
		dialog.dialog(
			dialog.TYPE.SELECT,
			'Select queue',
			'Please select a queue to move the slide to.',
			(status, val) => {
				if (!status) { return; }
				sel_slide.set({'queue_name': val});
				var cb = () => {
					sel_slide.save((err) => {
						if (API.handle_disp_error(err)) { return; }
						sel_slide = null;
						set_inputs(null);
						disable_controls();
						TL.update();
					});
				}
				if (!sel_slide_unsaved_confirm(cb)) { cb(); }
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
		TL.update();
	});
}

function inputs_setup(ready) {
	/*
	*  Setup all editor inputs and input validators etc.
	*/

	// Setup validators for the name and index inputs.
	name_sel = new val.ValidatorSelector(
		SLIDE_NAME,
		SLIDE_NAME_GRP,
		[new val.StrValidator({
			min: 1,
			max: null,
			regex: null
		}, "The name is too short."),
		new val.StrValidator({
			min: null,
			max: API.SERVER_LIMITS.SLIDE_NAME_MAX_LEN,
			regex: null
		}, "The name is too long."),
		new val.StrValidator({
			min: null,
			max: null,
			regex: /^[A-Za-z0-9_-]*$/
		}, "The name contains invalid characters.")]
	);
	index_sel = new val.ValidatorSelector(
		SLIDE_INDEX,
		SLIDE_INDEX_GRP,
		[new val.NumValidator({
			min: 0,
			max: null,
			nan: true,
			float: true
		}, "The index is too small."),
		new val.NumValidator({
			min: null,
			max: API.SERVER_LIMITS.SLIDE_MAX_INDEX,
			nan: true,
			float: true
		}, "The index is too large."),
		new val.NumValidator({
			min: null,
			max: null,
			nan: false,
			float: false
		}, "The index must be an integer value.")]
	);

	val_trigger = new val.ValidatorTrigger(
		[name_sel, index_sel],
		(valid) => {
			SLIDE_SAVE.prop('disabled', !valid);
		}
	);

	// Update enabled controls when scheduling is enabled.
	SLIDE_SCHED.change(enable_controls);

	// Setup the ACE editor with the Dawn theme + plaintext mode.
	SLIDE_INPUT = ace.edit('slide-input');
	SLIDE_INPUT.setTheme('ace/theme/dawn');
	SLIDE_INPUT.$blockScrolling = Infinity;

	// Setup the collaborators multiselect w/ validators.
	API.call(API.ENDP.USERS_LIST, {}, (data) => {
		if (API.handle_disp_error(data['error'])) { return; }
		SLIDE_COLLAB = new multiselect.MultiSelect(
			'slide-collab-group',
			'slide-collab',
			[new val.WhitelistValidator(
				{ wl: data['users'] },
				"This user doesn't exist."
			),
			new val.BlacklistValidator(
				{ bl: [API.CONFIG.user] },
				"You can't add yourself " +
				"as a collaborator."
			)],
			{
				'nodups': true,
				'maxopts': API.SERVER_LIMITS.SLIDE_MAX_COLLAB
			}
		);
		if (ready) { ready(); }
	});
}

function editor_setup() {
	util.setup_defaults();

	/*
	*  Add a listener for the 'beforeunload' event to make sure
	*  the user doesn't accidentally exit the page and lose changes.
	*/
	$(window).on('beforeunload', function(e) {
		if (!sel_slide_is_modified()) { return; }
		e.returnValue = "The selected slide is not saved. " +
				"Any changes will be lost if you exit " +
				"the page. Are you sure you want to " +
				"continue?";
		return e.returnValue;
	});

	inputs_setup(() => {
		// Disable inputs and setup update intervals.
		disable_controls();
		TL = new timeline.Timeline(API, slide_show);
		qsel.setup(API, TL);
		flag_editor_ready = true;
		console.log("LibreSignage: Editor ready.");
	});

	// Setup the live preview.
	LIVE_PREVIEW = new preview.Preview(
		'#slide-live-preview',
		'#slide-input',
		() => { return UI_DEFS.get('SLIDE_INPUT').get(); },
		(e) => {
			if (e) {
				MARKUP_ERR_DISPLAY.text(`>> Syntax error: ${e.message}`);
			} else {
				MARKUP_ERR_DISPLAY.text('');
			}
		}
	);
}

$(document).ready(() => {
	API = new api.API(
		null,	// Use default config.
		editor_setup
	);
});
