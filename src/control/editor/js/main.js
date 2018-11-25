/*
*  LibreSignage editor interface code. This file contains
*  all the main UI element handling for the editor.
*/

var $ = require('jquery');
var bootstrap = require('bootstrap');

var APIInterface = require('ls-api').APIInterface;
var APIError = require('ls-api').APIError;
var APIEndpoint = require('ls-api').APIEndpoints;
var APIUI = require('ls-api-ui');

var Slide = require('ls-slide').Slide;
var Queue = require('ls-queue').Queue;
var User = require('ls-user').User;
var Preview = require('./preview.js').Preview;
var Timeline = require('./timeline.js').Timeline;
var AssetUploader = require('./asset_uploader.js').AssetUploader;

var util = require('ls-util');
var val = require('ls-validator');
var dialog = require('ls-dialog');
var multiselect = require('ls-multiselect');
var uic = require('ls-uicontrol');
var sc = require('ls-shortcut');
var popup = require('ls-popup');

var ace_range = ace.require('ace/range');
var diags = require('./dialogs.js');


// Some sane default values for new slides.
const NEW_SLIDE_DEFAULTS = {
	'id': null,
	'name': 'NewSlide',
	'owner': null,
	'duration': 5000,
	'markup': '',
	'index': 0,
	'enabled': true,
	'sched': false,
	'sched_t_s': Math.round(Date.now()/1000),
	'sched_t_e': Math.round(Date.now()/1000),
	'animation': 0,
	'queue_name': '',
	'collaborators': [],
	'lock': null,
	'assets': []
};

// Editor status constants used by editor_status[_check]().
const ESTATUS = {
	NOSLIDE: 0,
	UNSAVED: 1,
	SAVED: 2
};

// DOM element jQuery selectors.
var QUEUE_SELECT          = $("#queue-select");
var QUEUE_CREATE          = $("#queue-create");
var QUEUE_VIEW            = $("#queue-view");
var QUEUE_REMOVE          = $("#queue-remove");

var SLIDE_NEW             = $("#btn-slide-new")
var SLIDE_PREVIEW         = $("#btn-slide-preview");
var SLIDE_SAVE            = $("#btn-slide-save");
var SLIDE_REMOVE          = $("#btn-slide-remove");
var SLIDE_CH_QUEUE        = $("#btn-slide-ch-queue");
var SLIDE_DUP             = $("#btn-slide-dup");
var SLIDE_CANT_EDIT       = $("#slide-cant-edit");
var SLIDE_READONLY        = $("#slide-readonly");
var SLIDE_EDIT_AS_COLLAB  = $("#slide-edit-as-collab");
var SLIDE_NAME            = $("#slide-name");
var SLIDE_NAME_GRP        = $("#slide-name-group");
var SLIDE_OWNER           = $("#slide-owner");
var SLIDE_DURATION        = $("#slide-duration");
var SLIDE_DURATION_GRP    = $("#slide-duration-group");
var SLIDE_INDEX           = $("#slide-index");
var SLIDE_INDEX_GRP       = $("#slide-index-group");
var SLIDE_EN              = $("#slide-enabled");
var SLIDE_SCHED           = $("#slide-sched");
var SLIDE_SCHED_DATE_S    = $("#slide-sched-date-s");
var SLIDE_SCHED_TIME_S    = $("#slide-sched-time-s");
var SLIDE_SCHED_DATE_E    = $("#slide-sched-date-e");
var SLIDE_SCHED_TIME_E    = $("#slide-sched-time-e");
var SLIDE_ANIMATION       = $("#slide-animation")
var PREVIEW_R_16x9        = $("#btn-preview-ratio-16x9");
var PREVIEW_R_4x3         = $("#btn-preview-ratio-4x3");
var MARKUP_ERR_DISPLAY    = $("#markup-err-display");
var LINK_QUICK_HELP       = $("#link-quick-help");
var CONT_QUICK_HELP       = $("#cont-quick-help");
var LINK_ADD_MEDIA        = $("#link-add-media");
var CLOSE_QUICK_HELP      = $("#close-quick-help");
var SLIDE_COLLAB          = null;
var SLIDE_INPUT           = null;
var preview          = null;

var QUICK_HELP = new popup.Popup($('#quick-help').get(0));

var API = null;            // API interface object.
var timeline = null;             // Timeline object.
var assetuploader = null; // Asset uploader object.

var qsel_queues = null;    // Queue selector queues list.

// Input validators.
var name_sel = null;
var index_sel = null;
var duration_sel = null;
var val_trigger = null;

var state = {
	slide_loading: false,
	editor_ready: false
};

var syn_err_id = null;     // Syntax error marker id.
var sel_slide = null;      // Selected slide.

// Editor UI definitions.
const UI = new uic.UIController({
	'PREVIEW_R_16x9': new uic.UIButton(
		elem = PREVIEW_R_16x9,
		perm = (d) => {
			return preview.ratio != '16x9';
		},
		enabler = null,
		attach = {
			'click': (e) => {
				UI.get('PREVIEW_R_16x9').enabled(false);
				UI.get('PREVIEW_R_4x3').enabled(true);
				preview.set_ratio('16x9');
			}
		},
		defer = defer_editor_ready
	),
	'PREVIEW_R_4x3': new uic.UIButton(
		elem = PREVIEW_R_4x3,
		perm = (d) => {
			return preview.ratio != '4x3';
		},
		enabler = null,
		attach = {
			'click': () => {
				UI.get('PREVIEW_R_16x9').enabled(true);
				UI.get('PREVIEW_R_4x3').enabled(false);
				preview.set_ratio('4x3');
			}
		},
		defer = defer_editor_ready
	),
	'SLIDE_NEW': new uic.UIButton(
		elem = SLIDE_NEW,
		perm = (d) => { return true; },
		enabler = null,
		attach = {
			'click': slide_new
		},
		defer = defer_editor_ready
	),
	'SLIDE_PREVIEW': new uic.UIButton(
		elem = SLIDE_PREVIEW,
		perm = (d) => { return d['s']; },
		enabler = null,
		attach = {
			'click': slide_preview
		},
		defer = defer_editor_ready
	),
	'SLIDE_SAVE': new uic.UIButton(
		elem = SLIDE_SAVE,
		perm = (d) => {
			return (
				d['v'] && (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				)
			);
		},
		enabler = null,
		attach = {
			'click': slide_save
		},
		defer = defer_editor_ready
	),
	'SLIDE_REMOVE': new uic.UIButton(
		elem = SLIDE_REMOVE,
		perm = (d) => {
			return !d['n']
				&& d['s']
				&& d['l']
				&& d['o']
				&& sel_slide.get('id') != null;
		},
		enabler = null,
		attach = {
			'click': slide_rm
		},
		defer = defer_editor_ready
	),
	'SLIDE_CH_QUEUE': new uic.UIButton (
		elem = SLIDE_CH_QUEUE,
		perm = (d) => { return !d['n'] && d['s'] && d['l'] && d['o']; },
		enabler = null,
		attach = {
			'click': slide_ch_queue
		},
		defer = defer_editor_ready
	),
	'SLIDE_DUP': new uic.UIButton(
		elem = SLIDE_DUP,
		perm = (d) => { return !d['n'] && d['s']; },
		enabler = null,
		attach = {
			'click': slide_dup
		},
		defer = defer_editor_ready
	),
	'SLIDE_CANT_EDIT': new uic.UIStatic(
		elem = SLIDE_CANT_EDIT,
		perm = (d) => { return !d['n'] && d['s'] && !d['o'] && !d['c']; },
		enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		attach = null,
		defer = null,
		getter = null,
		setter = null
	),
	'SLIDE_READONLY': new uic.UIStatic(
		elem = SLIDE_READONLY,
		perm = (d) => {
			return !d['n'] && d['s'] && !d['l'] && (d['o'] || d['c']);
		},
		enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		attach = null,
		defer = null,
		getter = null,
		setter = null
	),
	'SLIDE_EDIT_AS_COLLAB': new uic.UIStatic(
		elem = SLIDE_EDIT_AS_COLLAB,
		perm = (d) => {
			return (
				!d['n']
				&& d['s']
				&& d['l']
				&& d['c']
			)
		},
		enabler = (elem, s) => {
			elem.css('display', s ? 'block': 'none');
		},
		attach = null,
		defer = null,
		getter = null,
		setter = null
	),
	'SLIDE_NAME': new uic.UIInput(
		elem = SLIDE_NAME,
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, data) => {
			return elem.val() != data.get('name');
		},
		getter = (elem) => { return elem.val(); },
		setter = (elem, s) => {
			elem.val(s.get('name'));
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_OWNER': new uic.UIInput(
		elem = SLIDE_OWNER,
		perm = null,
		enabler = null,
		attach = null,
		defer = null,
		mod = null,
		getter = null,
		setter = (elem, s) => {
			elem.val(s.get('owner'));
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_DURATION': new uic.UIInput(
		elem = SLIDE_DURATION,
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var dur = parseFloat(elem.val(), 10);
			return dur != s.get('duration')/1000;
		},
		getter = (elem) => { return parseFloat(elem.val(), 10); },
		setter = (elem, s) => {
			var dur = parseFloat(s.get('duration'), 10);
			elem.val(dur/1000);
		},
		clearer = (elem) => { elem.val(1); }
	),
	'SLIDE_INDEX': new uic.UIInput(
		elem = SLIDE_INDEX,
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var tmp = parseInt(elem.val(), 10);
			return tmp != s.get('index');
		},
		getter = (elem) => { return parseInt(elem.val(), 10); },
		setter = (elem, s) => {
			elem.val(s.get('index'));
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_EN': new uic.UIInput(
		elem = SLIDE_EN,
		perm = (d) => {
			if (!UI.get('SLIDE_SCHED').get()) {
				return (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				);
			} else {
				return false;
			}
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			return elem.prop('checked') != s.get('enabled');
		},
		getter = (elem) => { return elem.prop('checked'); },
		setter = (elem, s) => {
			elem.prop('checked', s.get('enabled'));
		},
		clearer = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED': new uic.UIInput(
		elem = SLIDE_SCHED,
		perm = (d) => { return !d['s'] && d['l'] && (d['o'] || d['c']); },
		enabler = null,
		attach = {
			'change': () => { update_controls(); }
		},
		defer = defer_editor_ready,
		mod = (elem, s) => {
			return elem.prop('checked') != s.get('sched');
		},
		getter = (elem) => { return elem.prop('checked'); },
		setter = (elem, s) => {
			elem.prop('checked', s.get('sched'));
		},
		clearer = (elem) => { elem.prop('checked', false); }
	),
	'SLIDE_SCHED_DATE_S': new uic.UIInput(
		elem = SLIDE_SCHED_DATE_S,
		perm = (d) => {
			return (
				UI.get('SLIDE_SCHED').get()
				&& (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				)
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[0];
			return elem.val() != tmp;
		},
		getter = (elem) => { return elem.val(); },
		setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[0];
			elem.val(tmp);
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_S': new uic.UIInput(
		elem = SLIDE_SCHED_TIME_S,
		perm = (d) => {
			return (
				UI.get('SLIDE_SCHED').get()
				&& (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				)
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[1];
			return elem.val() != tmp;
		},
		getter = (elem) => { return elem.val(); },
		setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_s'))[1];
			elem.val(tmp);
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_DATE_E': new uic.UIInput(
		elem = SLIDE_SCHED_DATE_E,
		perm = (d) => {
			return (
				UI.get('SLIDE_SCHED').get()
				&& (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				)
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[0];
			return elem.val() != tmp;
		},
		getter = (elem) => { return elem.val(); },
		setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[0];
			elem.val(tmp);
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_SCHED_TIME_E': new uic.UIInput(
		elem = SLIDE_SCHED_TIME_E,
		perm = (d) => {
			return (
				UI.get('SLIDE_SCHED').get()
				&& (
					(!d['n'] && !d['s'])
					|| (d['l'] && (d['o'] || d['c']))
				)
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[1];
			return elem.val() != tmp;
		},
		getter = (elem) => { return elem.val(); },
		setter = (elem, s) => {
			var tmp = util.tstamp_to_datetime(s.get('sched_t_e'))[1];
			elem.val(tmp);
		},
		clearer = (elem) => { elem.val(''); }
	),
	'SLIDE_ANIMATION': new uic.UIInput(
		elem = SLIDE_ANIMATION,
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
			);
		},
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, s) => {
			var anim = parseInt(elem.val(), 10);
			return anim != s.get('animation');
		},
		getter = (elem) => { return parseInt(elem.val(), 10); },
		setter = (elem, s) => {
			elem.val(s.get('animation'));
		},
		clearer = (elem) => { elem.val(0); }
	),
	'SLIDE_COLLAB': new uic.UIInput(
		elem = () => { return SLIDE_COLLAB; },
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && d['o'])
			);
		},
		enabler = (elem, s) => {
			if (s) {
				elem.enable();
			} else {
				elem.disable();
			}
		},
		attach = null,
		defer = null,
		mod = (elem, s) => {
			return !util.sets_eq(
				elem.selected,
				s.get('collaborators')
			);
		},
		getter = (elem) => { return elem.selected; },
		setter = (elem, s) => {
			elem.set(s.get('collaborators'));
		},
		clearer = (elem) => { elem.set([]); }
	),
	'SLIDE_INPUT': new uic.UIInput(
		elem = () => { return SLIDE_INPUT; },
		perm = (d) => {
			return (
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
			);
		},
		enabler = (elem, s) => { elem.setReadOnly(!s); },
		attach = null,
		defer = null,
		mod = (elem, s) => {
			return elem.getValue() != s.get('markup');
		},
		getter = (elem) => { return elem.getValue(); },
		setter = (elem, s) => {
			elem.setValue(s.get('markup'));
			SLIDE_INPUT.clearSelection();
		},
		clearer = (elem) => {
			elem.setValue('');
			SLIDE_INPUT.clearSelection();
		}
	),
	'LINK_QUICK_HELP': new uic.UIButton(
		elem = () => { return LINK_QUICK_HELP; },
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': (e) => {
				e.preventDefault(); // Don't scroll up.
				POPUPS.get('QUICK_HELP').enabled(true);
			}
		},
		defer = defer_editor_ready
	),
	'LINK_ADD_MEDIA': new uic.UIButton(
		elem = () => { return LINK_ADD_MEDIA; },
		perm = (d) => {
			return !d['n'] && (d['o'] || d['c']);
		},
		enabler = (elem, s) => {
			if (s) {
				elem.removeClass('disabled');
			} else {
				elem.addClass('disabled');
			}
		},
		attach = {
			'click': (e) => {
				e.preventDefault(); // Don't scroll up.
				if (!$(e.target).hasClass('disabled')) {
					if (sel_slide) {
						assetuploader.show(sel_slide.get('id'));
					} else {
						assetuploader.show(null);
					}
				}
			}
		},
		defer = defer_editor_ready
	)
});

// Popup UI definitions.
const POPUPS = new uic.UIController({
	'QUICK_HELP': new uic.UIStatic(
		elem = () => { return QUICK_HELP; },
		perm = () => { return false; },
		enabler = (elem, s) => { elem.visible(s); },
		attach = null,
		defer = null
	)
})

// Queue selector UI definitions.
const QSEL_UI = new uic.UIController({
	'QUEUE_SELECT': new uic.UIInput(
		elem = QUEUE_SELECT,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'change': () => {
				queue_select(
					QSEL_UI.get('QUEUE_SELECT').get(),
					true,
					null
				);
			}
		},
		defer = defer_editor_ready,
		mod = null,
		getter = (elem) => { return elem.val(); },
		setter = (elem, value) => { elem.val(value); },
		clearer = () => { elem.val(''); }
	),
	'QUEUE_CREATE': new uic.UIButton(
		elem = QUEUE_CREATE,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': queue_create
		},
		defer = defer_editor_ready
	),
	'QUEUE_VIEW': new uic.UIButton(
		elem = QUEUE_VIEW,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': queue_view
		},
		defer = defer_editor_ready
	),
	'QUEUE_REMOVE': new uic.UIButton(
		elem = QUEUE_REMOVE,
		perm = (d) => {
			return d['o'] && timeline.get_queue();
		},
		enabler = null,
		attach = {
			'click': queue_remove
		},
		defer = defer_editor_ready
	),
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

function defer_editor_ready() {
	return !state.editor_ready;
};

function update_controls() {
	/*
	*  Enable editor controls using the UIController system.
	*  The array passed to the UIElem.perm() functions contains
	*  the following values.
	*
	*  n = Is the current slide object null?
	*  o = Is the current user the owner of this slide or an admin?
	*  c = Is the current user a collaborator of this slide?
	*  l = Is the current slide locked by the current user?
	*  s = Is the current slide saved?
	*  v = Is the current editor data valid?
	*/

	let user = API.get_session().get_user();
	UI.all(
		function(d) { this.state(d); },
		{
			'n': sel_slide === null,
			'o': (
				sel_slide !== null
				&& (
					!sel_slide.has('owner')
					|| sel_slide.get('owner') === user.get_user()
					|| user.get_groups().includes('admin')
				)
			),
			'c': (
				sel_slide !== null &&
				sel_slide.get('collaborators').includes(user.get_user())
			),
			'l': (
				sel_slide !== null
				&& sel_slide.is_locked_from_here()
			),
			's': (
				sel_slide !== null
				&& sel_slide.has('id')
			),
			'v': val_trigger.is_valid()
		}
	);

	if (sel_slide != null) {
		name_sel.enable();
		index_sel.enable();
		duration_sel.enable();
	} else {
		name_sel.disable();
		index_sel.disable();
		duration_sel.disable();
	}
}

function set_inputs(s) {
	/*
	*  Display the data of 'slide' on the editor inputs.
	*/
	if (!s) {
		UI.all(function() { this.clear(); }, null, 'input');
	} else {
		UI.all(function(data) { this.set(data); }, s, 'input');
	}
}

function editor_status() {
	let ret = false;
	if (!sel_slide) {
		return ESTATUS.NOSLIDE;
	} else {
		UI.all(
			function() {
				if (this.is_mod(sel_slide)) {
					ret = true;
					return false;
				}
			},
			null,
			'input'
		);
		if (ret) {
			return ESTATUS.UNSAVED;
		} else {
			return ESTATUS.SAVED;
		}
	}
}

function editor_status_check(arr) {
	let tmp = editor_status();
	for (let s of arr) {
		if (tmp == ESTATUS[s]) { return true; }
	}
	return false;
}

async function slide_update() {
	/*
	*  Only update slide data if the slide is already loaded.
	*/
	console.log(`LibreSignage: Update slide.`);
	if (!sel_slide.is_locked_from_here()) {
		try {
			await sel_slide.lock_acquire(true);
		} catch (e) {} // Continue even with lock errors.
	}
	await sel_slide.fetch();
}

async function slide_load(s) {
	/*
	*  Load a new slide.
	*/
	console.log(`LibreSignage: Show slide '${s}'.`);
	if (sel_slide != null && sel_slide.is_locked_from_here()) {
		try {
			await sel_slide.lock_release();
		} catch (e) {} // Continue even with errors.
	}

	// Load the new slide w/ a lock.
	sel_slide = new Slide(API);
	try {
		await sel_slide.load(s, true, true);
	} catch (e) {
		switch (e.response.error) {
			case APIError.codes.API_E_NOT_AUTHORIZED:
			case APIError.codes.API_E_LOCK:
				// or w/o a lock
				await sel_slide.load(s, false, false);
				break;
			default:
				throw e;
				break;
		}
	}
}

async function slide_show(s, no_popup) {
	/*
	*  Show the slide 's'. If the current slide has unsaved
	*  changes, this function displays an 'Unsaved changes'
	*  dialog and only selects the requested slide if the
	*  user clicks OK. If the slide is not yet locked by
	*  anyone, this function locks the slide so that it's
	*  writable by the logged in user. Otherwise the slide
	*  is not locked and is loaded read-only.
	*/
	if (state.slide_loading) { return; }
	new Promise((resolve, reject) => {
		if (!no_popup && editor_status_check(['UNSAVED'])){
			diags.DIALOG_SLIDE_UNSAVED(
				(status, val) => {
					if (status) { resolve(); } else { reject(); }
				}
			).show();
		} else {
			resolve();
		}
	}).then(async () => {
		state.slide_loading = true;
		try {
			if (sel_slide != null && s == sel_slide.get('id')) {
				await slide_update();
			} else {
				await slide_load(s);
			}
		} catch (e) {
			APIUI.handle_error(e);
			slide_hide();
			state.slide_loading = false;
			return;
		}
		set_inputs(sel_slide);
		update_controls();
		preview.update();
		timeline.select(sel_slide.get('id'));
		state.slide_loading = false;			
	}).catch(() => {});
}

function slide_hide() {
	/*
	*  Hide the current slide, ie. set sel_slide = null
	*  and clear the editor inputs etc.
	*/
	console.log('LibreSignage: Hide slide.');
	sel_slide = null;
	timeline.select(null);
	set_inputs(null);
	update_controls();
	preview.update();
}

async function slide_rm() {
	/*
	*  Remove the selected slide. This function asks for
	*  confirmation from the user before actually removing
	*  the slide.
	*/
	new Promise((resolve, reject) => {
		dialog.dialog(
			dialog.TYPE.CONFIRM,
			"Delete slide?",
			`Are you sure you want to delete ` +
			`slide '${sel_slide.get("name")}'.`,
			(status, val) => {
				if (status) {
					resolve();
				} else {
					reject();
				}
			}
		);
	}).then(async () => {
		try {
			await sel_slide.remove(null);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		console.log(
			`LibreSignage: Deleted slide '${sel_slide.get('id')}'.`
		);
		slide_hide();
		await timeline.update();
	}).catch(() => {});
}

async function slide_new() {
	/*
	*  Create a new slide. Note that this function doesn't save
	*  the slide server-side. The user must manually save the
	*  slide afterwards.
	*/
	if (!timeline.get_queue()) {
		dialog.dialog(
			dialog.TYPE.ALERT,
			'Please create a queue',
			'You must create a queue before you can ' +
			'add a slide to one.',
			null
		);
		return;
	}

	new Promise((resolve, reject) => {
		if (editor_status_check(['UNSAVED'])) {
			diags.DIALOG_SLIDE_UNSAVED((status, val) => {
				if (status) { resolve(); } else { reject(); }
			}).show();		
		} else {
			resolve();
		}
	}).then(async () => {
		console.log("LibreSignage: Create slide!");
		if (sel_slide != null && sel_slide.is_locked_from_here()) {
			try {
				await sel_slide.lock_release(null);
			} catch (e) {} // Continue even with errors.
		}
		timeline.select(null);
		sel_slide = new Slide(API);
		sel_slide.set(NEW_SLIDE_DEFAULTS);
		set_inputs(sel_slide);
		update_controls();
	}).catch(() => {});
}

async function slide_save() {
	/*
	*  Save the currently selected slide.
	*/
	if (
		UI.get('SLIDE_INPUT').get().length >
		API.limits.SLIDE_MARKUP_MAX_LEN
	) {
		diags.DIALOG_MARKUP_TOO_LONG(
			API.limits.SLIDE_MARKUP_MAX_LEN
		).show();
		return;
	}

	sel_slide.set({
		'name':          UI.get('SLIDE_NAME').get(),
		'duration':      UI.get('SLIDE_DURATION').get()*1000,
		'index':         UI.get('SLIDE_INDEX').get(),
		'markup':        UI.get('SLIDE_INPUT').get(),
		'enabled':       UI.get('SLIDE_EN').get(),
		'sched':         UI.get('SLIDE_SCHED').get(),
		'sched_t_s': util.datetime_to_tstamp(
			UI.get('SLIDE_SCHED_DATE_S').get(),
			UI.get('SLIDE_SCHED_TIME_S').get()
		),
		'sched_t_e': util.datetime_to_tstamp(
			UI.get('SLIDE_SCHED_DATE_E').get(),
			UI.get('SLIDE_SCHED_TIME_E').get()
		),
		'animation':     UI.get('SLIDE_ANIMATION').get(),
		'queue_name':    timeline.get_queue().get_name(),
		'collaborators': UI.get('SLIDE_COLLAB').get()
	});

	try {
		await sel_slide.save();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	console.log(`LibreSignage: Saved '${sel_slide.get("id")}'.`);
	update_controls();
	await timeline.update();
	slide_show(sel_slide.get('id'), true);
}

function slide_preview() {
	// Preview the current slide in a new window.
	window.open(`/app/?preview=${sel_slide.get('id')}`);
}

async function slide_ch_queue() {
	/*
	*  Change the queue of the selected slide. This function
	*  prompts the user for the new queue.
	*/
	let qd = null;
	var queues = {};

	try {
		qd = await Queue.get_queues(API);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	console.log(qd);

	qd.sort();
	for (let q of qd) {
		if (q != sel_slide.get('queue_name')) { queues[q] = q; }
	}

	new Promise((resolve, reject) => {
		dialog.dialog(
			dialog.TYPE.SELECT,
			'Select queue',
			'Please select a queue to move the slide to.',
			(status, val) => {
				if (status) {
					resolve(val);
				} else {
					reject();
				}
			},
			queues
		);
	}).then((val) => {
		sel_slide.set({'queue_name': val});
		return new Promise((resolve, reject) => {
			if (editor_status_check(['UNSAVED'])) {
				diags.DIALOG_SLIDE_UNSAVED((status, val) => {
					if (status) {
						resolve();
					} else {
						reject();
					}
				}).show();
			} else { resolve(); }
		});
	}).then(async () => {
		try {
			await sel_slide.save();
			slide_hide();
			await timeline.update();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
	}).catch(() => {});
}

async function slide_dup() {
	/*
	*  Duplicate the selected slide.
	*/
	try {
		sel_slide = await sel_slide.dup();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	set_inputs(sel_slide);
	await timeline.update();
}

function syn_err_highlight(from, to) {
	/*
	*  Create an editor syntax error marker for the
	*  lines from-to. Returns the marker ID.
	*/
	var sess = UI.get('SLIDE_INPUT').get_elem().session;
	return syn_err_id = sess.addMarker(
		new ace_range.Range(from, 0, to, 10),
		'syntax-error-highlight',
		'fullLine'
	);
}

function syn_err_clear(id) {
	/*
	*  Remove the editor syntax error marker 'id'.
	*/
	if (id) {
		UI.get('SLIDE_INPUT').get_elem().session.removeMarker(id);
	}
}

async function queue_select(name, confirm) {
	/*
	*  Select the queue 'name'. If 'confirm' == true and an unsaved
	*  slide is selected, the user will be asked for confirmation
	*  first.
	*/
	new Promise((resolve, reject) => {
		if (editor_status_check(['UNSAVED']) && confirm) {
			diags.DIALOG_SLIDE_UNSAVED(
				(status, val) => {
					if (status) {
						resolve();
					} else {
						reject();
					}
				}
			).show();
		} else {
			resolve();
		}
	}).then(async () => {
		console.log(`LibreSignage: Select queue '${name}'.`)
		await timeline.show(name);
		slide_hide();
		update_qsel_ctrls();
	}).catch(() => {});
}

async function queue_create() {
	/*
	*  Create a new queue and select it. If the current slide in
	*  the editor is unsaved, the user is asked for confirmation
	*  first.
	*/
	new Promise((resolve, reject) => {
		if (editor_status_check(['UNSAVED'])) {
			diags.DIALOG_SLIDE_UNSAVED(
				(status, val) => {
					if (status) {
						resolve();
					} else {
						reject();
					}
				}
			).show();
		} else {
			resolve();
		}
	}).then(() => {
		return new Promise((resolve, reject) => {
			dialog.dialog(
				dialog.TYPE.PROMPT,
				'Create queue',
				'Queue name',
				(status, val) => {
					if (status) {
						resolve(val);
					} else {
						reject();
					}
				},
				[new val.StrValidator({
					min: null,
					max: null,
					regex: /^[A-Za-z0-9_-]*$/
				}, "Invalid characters in queue name."),
				new val.StrValidator({
					min: 1,
					max: null,
					regex: null,
				}, "The queue name is too short."),
				new val.StrValidator({
					min: null,
					max: API.limits.QUEUE_NAME_MAX_LEN,
					regex: null
				}, "The queue name is too long."),
				new val.BlacklistValidator({
					bl: qsel_queues
				}, "This queue already exists.")]
			);
		});
	}).then(async (val) => {
		let resp = null;
		try {
			resp = await API.call(
				APIEndpoints.QUEUE_CREATE,
				{'name': val}
			);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		console.log(`LibreSignage: Created queue '${val}'.`);

		// Select the new queue.
		await update_qsel(false);		
		QSEL_UI.get('QUEUE_SELECT').set(val);
		await queue_select(val, false, null);
	}).catch(() => {});
}

async function queue_remove() {
	/*
	*  Remove the selected queue. If the current slide in the
	*  editor is unsaved, the user is asked for confirmation
	*  first.
	*/
	let callback = () => {

	};
	new Promise((resolve, reject) => {
		if (editor_status_check(['UNSAVED'])) {
			diags.DIALOG_SLIDE_UNSAVED(
				(status, val) => {
					if (status) {
						resolve();
					} else {
						reject();
					}
				}
			).show();
		} else { resolve(); }
	}).then(() => {
		return new Promise((resolve, reject) => {
			dialog.dialog(
				dialog.TYPE.CONFIRM,
				'Delete queue',
				'Delete the selected queue and all the slides in it?',
				(status) => {
					if (status) {
						resolve();
					} else {
						reject();
					}
				},
				null
			);
		});
	}).then(async () => {
		try {
			await API.call(
				APIEndpoint.QUEUE_REMOVE,
				{'name': timeline.get_queue().get_name()}
			);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		await update_qsel(true, null);
	}).catch(() => {});
}

function queue_view() {
	window.open(`/app/?q=${timeline.get_queue().get_name()}`);
}

async function update_qsel(show_initial) {
	/*
	*  Update the queue selector options.
	*/
	qsel_queues = await Queue.get_queues(API);
	qsel_queues.sort();

	QUEUE_SELECT.html('');
	for (let q of qsel_queues) {
		QUEUE_SELECT.append(`<option value="${q}">${q}</option>`);
	}
	if (show_initial && qsel_queues.length) {
		await queue_select(qsel_queues[0], false);
	} else {
		await queue_select(null, false);
	}
}

function update_qsel_ctrls() {
	// Update queue selector controls.
	QSEL_UI.all(
		function() {
			this.state({
				'o': (
					timeline.get_queue()
					&& (
						timeline.get_queue().get_owner()
						== API.get_session().get_user().get_user()
					)
				)
			});
		},
		null,
		'button'
	);
}

async function ui_setup() {
	/*
	*  Setup the editor UI.
	*/
	preview = new Preview(
		'#slide-live-preview',
		'#slide-input',
		() => { return UI.get('SLIDE_INPUT').get(); },
		(e) => {
			if (e) {
				syn_err_id = syn_err_highlight(e.line(), e.line());
				MARKUP_ERR_DISPLAY.text('>> ' + e.toString(1));
			} else {
				syn_err_id = syn_err_clear(syn_err_id);
				MARKUP_ERR_DISPLAY.text('');
			}
		},
		false
	);
	timeline = new Timeline(
		API,
		'#timeline',
		slide_show
	);
	assetuploader = new AssetUploader(
		API,
		'#asset-uploader'
	);

	// Setup validators for the name, index and duration inputs.
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
			max: API.limits.SLIDE_NAME_MAX_LEN,
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
			max: API.limits.SLIDE_MAX_INDEX,
			nan: true,
			float: true
		}, "The index is too large."),
		new val.NumValidator({
			min: null,
			max: null,
			nan: true,
			float: false
		}, 'The index must be an integer value.'),
		new val.NumValidator({
			min: null,
			max: null,
			nan: false,
			float: false
		}, 'You must specify an index.')]
	);
	duration_sel = new val.ValidatorSelector(
		SLIDE_DURATION,	
		SLIDE_DURATION_GRP,
		[new val.NumValidator(
			{
				min: API.limits.SLIDE_MIN_DURATION/1000,
				max: null,
				nan: true,
				float: true
			},
			`The duration is too short. The minimum duration ` +
			`is ${API.limits.SLIDE_MIN_DURATION/1000}s.`
		),
		new val.NumValidator(
			{
				min: null,
				max: API.limits.SLIDE_MAX_DURATION/1000,
				nan: true,
				float: true
			},
			`The duration is too long. The maximum duration ` +
			`is ${API.limits.SLIDE_MAX_DURATION/1000}s.`
		),
		new val.NumValidator({
			min: null,
			max: null,
			nan: false,
			float: true
		}, 'You must specify a duration.')]
	);

	/*
	*  Initially disable the validators.
	*  update_controls() enables them.
	*/
	name_sel.disable();
	index_sel.disable();
	duration_sel.disable();

	val_trigger = new val.ValidatorTrigger(
		[name_sel, index_sel, duration_sel],
		(valid) => { update_controls(); }
	);

	// Setup the ACE editor with the Dawn theme + plaintext mode.
	SLIDE_INPUT = ace.edit('slide-input');
	SLIDE_INPUT.setTheme('ace/theme/dawn');
	SLIDE_INPUT.$blockScrolling = Infinity;

	let users = null;
	try {
		users = User.list_all(API);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	SLIDE_COLLAB = new multiselect.MultiSelect(
		'slide-collab-group',
		'slide-collab',
		[new val.StrValidator(
			{ min: 1, max: null, regex: null },
			'', true
		),
		new val.WhitelistValidator(
			{ wl: users },
			"This user doesn't exist."
		),
		new val.BlacklistValidator(
			{ bl: [API.get_session().get_user().get_user()] },
			"You can't add yourself " +
			"as a collaborator."
		)],
		{
			'nodups': true,
			'maxopts': API.limits.SLIDE_MAX_COLLAB
		}
	);

	// Finish validator setup.
	val_trigger.trigger();
}

async function setup() {
	util.setup_defaults();

	/*
	*  Add a listener for the 'beforeunload' event to make sure
	*  the user doesn't accidentally exit the page and lose changes.
	*/
	$(window).on('beforeunload', function(e) {
		event.preventDefault();
		if (editor_status_check(['NOSLIDE', 'SAVED'])) { return; }
		e.returnValue = "The selected slide is not saved. " +
				"Any changes will be lost if you exit " +
				"the page. Are you sure you want to " +
				"continue?";
		return e.returnValue;
	});

	// Release slide lock on exit.
	$(window).on('unload', function(e) {
		if (sel_slide != null && sel_slide.is_locked_from_here()) {
			sel_slide.lock_release();
		}
	});

	// Setup inputs and other UI elements.
	try {
		await ui_setup();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	try {
		await update_qsel(true);
	} catch (e) {
		APIUI.handle_error(e);
	}
	update_controls();
	state.editor_ready = true;
	console.log("LibreSignage: Editor ready.");
}

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
		await setup();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
});
