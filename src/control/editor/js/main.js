/*
*  LibreSignage editor interface code. This file contains
*  all the main UI element handling for the editor.
*/

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
var popup = require('ls-popup');

var ace_range = ace.require('ace/range');

var timeline = require('./timeline.js');
var preview = require('./preview.js');
var diags = require('./dialogs.js');

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
const QUEUE_SELECT				= $("#queue-select");
const QUEUE_CREATE				= $("#queue-create");
const QUEUE_VIEW				= $("#queue-view");
const QUEUE_REMOVE				= $("#queue-remove");

const SLIDE_NEW					= $("#btn-slide-new")
const SLIDE_PREVIEW				= $("#btn-slide-preview");
const SLIDE_SAVE				= $("#btn-slide-save");
const SLIDE_REMOVE				= $("#btn-slide-remove");
const SLIDE_CH_QUEUE			= $("#btn-slide-ch-queue");
const SLIDE_DUP					= $("#btn-slide-dup");
const SLIDE_CANT_EDIT			= $("#slide-cant-edit");
const SLIDE_READONLY			= $("#slide-readonly");
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
const LINK_QUICK_HELP			= $("#link-quick-help");
const CONT_QUICK_HELP			= $("#cont-quick-help");
const LINK_ADD_MEDIA			= $("#link-add-media");
const CLOSE_QUICK_HELP			= $("#close-quick-help");
var SLIDE_COLLAB				= null;
var SLIDE_INPUT					= null;
var LIVE_PREVIEW				= null;

var QUICK_HELP = new popup.Popup($('#quick-help').get(0));
var ASSET_UPLOADER = new popup.Popup($('#asset-uploader').get(0));

var API = null; // API interface object.
var TL = null;  // Timeline object.

var qsel_queues = null; // Queue selector queues list.

// Input validator selectors.
var name_sel = null;
var index_sel = null;
var sel_slide = null;

var syn_err_id = null;          // Syntax error marker id.
var flag_slide_loading = false; // Slide loading flag.
var flag_editor_ready = false;  // Editor ready flag.

var defer_editor_ready = () => { return !flag_editor_ready; };

// Editor UI definitions.
const UI_DEFS = new uic.UIController({
	'PREVIEW_R_16x9': new uic.UIButton(
		elem = PREVIEW_R_16x9,
		perm = (d) => {
			return LIVE_PREVIEW.ratio != '16x9';
		},
		enabler = null,
		attach = {
			'click': (e) => {
				UI_DEFS.get('PREVIEW_R_16x9').enabled(false);
				UI_DEFS.get('PREVIEW_R_4x3').enabled(true);
				LIVE_PREVIEW.set_ratio('16x9');
			}
		},
		defer = defer_editor_ready
	),
	'PREVIEW_R_4x3': new uic.UIButton(
		elem = PREVIEW_R_4x3,
		perm = (d) => {
			return LIVE_PREVIEW.ratio != '4x3';
		},
		enabler = null,
		attach = {
			'click': () => {
				UI_DEFS.get('PREVIEW_R_16x9').enabled(true);
				UI_DEFS.get('PREVIEW_R_4x3').enabled(false);
				LIVE_PREVIEW.set_ratio('4x3');
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
				(!d['n'] && !d['s'])
				|| (d['l'] && (d['o'] || d['c']))
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
	'SLIDE_TIME': new uic.UIInput(
		elem = SLIDE_TIME,
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
			var time = parseInt(elem.val(), 10);
			return time != s.get('time')/1000;
		},
		getter = (elem) => { return parseInt(elem.val(), 10); },
		setter = (elem, s) => {
			var time = parseInt(s.get('time'), 10);
			elem.val(time/1000);
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
			if (!UI_DEFS.get('SLIDE_SCHED').get()) {
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
		attach = null,
		defer = null,
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
				UI_DEFS.get('SLIDE_SCHED').get()
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
				UI_DEFS.get('SLIDE_SCHED').get()
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
				UI_DEFS.get('SLIDE_SCHED').get()
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
				UI_DEFS.get('SLIDE_SCHED').get()
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
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': (e) => {
				e.preventDefault(); // Don't scroll up.
				POPUPS.get('ASSET_UPLOADER').enabled(true);
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
	),
	'ASSET_UPLOADER': new uic.UIStatic(
		elem = () => { return ASSET_UPLOADER; },
		perm => () => { return false; },
		enabler = (elem, s) => { elem.visible(s); },
		attach = null,
		defer = null
	)
})

// Queue selector UI definitions.
const QSEL_UI_DEFS = new uic.UIController({
	'QUEUE_SELECT': new uic.UIInput(
		elem = QUEUE_SELECT,
		perm = () => { return true; },
		enabler = null,
		attach = null,
		defer = null,
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
			return d['o'] && TL.queue;
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
	*/

	let user = API.CONFIG.user;

	UI_DEFS.all(
		function(d) { this.state(d); },
		{
			'n': sel_slide === null,
			'o': (
				sel_slide !== null
				&& (
					!sel_slide.has('owner')
					|| sel_slide.get('owner') === user.user
					|| user.groups.includes('admin')
				)
			),
			'c': (
				sel_slide !== null &&
				sel_slide.get('collaborators').includes(user.user)
			),
			'l': (
				sel_slide !== null
				&& sel_slide.is_locked_from_here()
			),
			's': (
				sel_slide !== null
				&& sel_slide.has('id')
			)
		}
	);
	if (sel_slide !== null) {
		name_sel.enable();
		index_sel.enable();
	} else {
		name_sel.disable();
		index_sel.disable();
	}
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

function editor_status() {
	let ret = false;
	if (!sel_slide) {
		return ESTATUS.NOSLIDE;
	} else {
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

function slide_show(s, no_popup) {
	/*
	*  Show the slide 's'. If the current slide has unsaved
	*  changes, this function displays an 'Unsaved changes'
	*  dialog and only selects the requested slide if the
	*  user clicks OK. If the slide is not yet locked by
	*  anyone, this function locks the slide so that it's
	*  writable by the logged in user. Otherwise the slide
	*  is not locked and is loaded read-only.
	*/
	var cb = () => {
		let error = () => {
			console.error('LibreSignage: API error.');
			slide_hide();
			flag_slide_loading = false;
		}
		let success = () => {
			set_inputs(sel_slide);
			update_controls();
			LIVE_PREVIEW.update();
			TL.select(sel_slide.get('id'));
			flag_slide_loading = false;
		}

		if (sel_slide !== null && s == sel_slide.get('id')) {
			/*
			*  Don't fully reload the slide if it's the same one
			*  that's already loaded. Just fetch the new data,
			*  acquire a lock if not already locked and update the
			*  UI.
			*/
			console.log(`LibreSignage: Update slide '${s}'.`);
			if (!sel_slide.is_locked_from_here()) {
				sel_slide.lock_acquire(true, err => {
					/*
					*  Doesn't matter whether locking succeeded or not.
					*  Fetch the new slide data anyway.
					*/
					sel_slide.fetch(err => err ? error() : success());
				});
			} else {
				sel_slide.fetch(err => err ? error() : success());
			}
		} else {
			console.log(`LibreSignage: Show slide '${s}'.`);
			flag_slide_loading = true;
			if (sel_slide !== null && sel_slide.is_locked_from_here()) {
				sel_slide.lock_release(null);
			}

			// Load the new slide w/ a lock.
			sel_slide = new slide.Slide(API);
			sel_slide.load(s, true, true, err_1 => {
				switch (err_1) {
					case API.ERR.API_E_OK:
						success();
						break;
					case API.ERR.API_E_NOT_AUTHORIZED:
					case API.ERR.API_E_LOCK:
						// or w/o a lock
						sel_slide.load(
							s,
							false,
							false,
							err_2 => err_2 ? error() : success()
						);
						break;
					default:
						error();
						break;
				}
			});
		}
	}

	if (flag_slide_loading) { return; }
	if (!no_popup && editor_status_check(['UNSAVED'])) {
		diags.DIALOG_SLIDE_UNSAVED(
			(status, val) => { if (status) { cb(); } }
		).show();
	} else { cb(); }
}

function slide_hide() {
	/*
	*  Hide the current slide, ie. set sel_slide = null
	*  and clear the editor inputs etc.
	*/
	console.log('LibreSignage: Hide slide.');
	sel_slide = null;
	TL.select(null);
	set_inputs(null);
	update_controls();
	LIVE_PREVIEW.update();
}

function slide_rm() {
	/*
	*  Remove the selected slide. This function asks for confirmation
	*  from the user before actually removing the slide.
	*/
	dialog.dialog(
		dialog.TYPE.CONFIRM,
		"Delete slide?",
		`Are you sure you want to delete ` +
		`slide '${sel_slide.get("name")}'.`,
		(status, val) => {
			if (!status) { return; }
			sel_slide.remove(null, stat => {
				if (API.handle_disp_error(stat)) { return; }
				//$(`#slide-btn-${sel_slide.get('id')}`).remove();
				console.log(
					`LibreSignage: Deleted slide ` +
					`'${sel_slide.get('id')}'.`
				);
				slide_hide();
				TL.update();
			});
		}
	);
}

function slide_new() {
	/*
	*  Create a new slide. Note that this function doesn't save
	*  the slide server-side. The user must manually save the
	*  slide afterwards.
	*/
	var cb = () => {
		console.log("LibreSignage: Create slide!");
		if (sel_slide !== null && sel_slide.is_locked_from_here()) {
			// Release the old slide lock.
			sel_slide.lock_release(null);
		}
		TL.select(null);
		sel_slide = new slide.Slide(API);
		sel_slide.set(NEW_SLIDE_DEFAULTS);
		set_inputs(sel_slide);
		update_controls();
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

	if (editor_status_check(['UNSAVED'])) {
		diags.DIALOG_SLIDE_UNSAVED((status, val) => {
			if (status) { cb(); }
		}).show();
	} else { cb(); }
}

function slide_save() {
	/*
	*  Save the currently selected slide.
	*/
	if (
		UI_DEFS.get('SLIDE_INPUT').get().length >
		API.SERVER_LIMITS.SLIDE_MARKUP_MAX_LEN
	) {
		diags.DIALOG_MARKUP_TOO_LONG(
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
		if (API.handle_disp_error(stat)) { return; }
		console.log(`LibreSignage: Saved '${sel_slide.get("id")}'.`);
		update_controls();
		TL.update();
		slide_show(sel_slide.get('id'), true);
	});
}

function slide_preview() {
	// Preview the current slide in a new window.
	window.open(`/app/?preview=${sel_slide.get('id')}`);
}

function slide_ch_queue() {
	/*
	*  Change the queue of the selected slide. This function
	*  prompts the user for the new queue.
	*/
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
						slide_hide();
						TL.update();
					});
				}
				if (editor_status_check(['UNSAVED'])) {
					diags.DIALOG_SLIDE_UNSAVED((status, val) => {
						if (status) { cb(); }
					}).show();
				} else { cb(); }
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

function syn_err_highlight(from, to) {
	/*
	*  Create an editor syntax error marker for the
	*  lines from-to. Returns the marker ID.
	*/
	var sess = UI_DEFS.get('SLIDE_INPUT').get_elem().session;
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
		UI_DEFS.get('SLIDE_INPUT').get_elem().session.removeMarker(id);
	}
}

function queue_select(name, confirm, ready) {
	/*
	*  Select the queue 'name'. If 'confirm' == true and an unsaved
	*  slide is selected, the user will be asked for confirmation
	*  first.
	*/
	var callback = () => {
		console.log(`LibreSignage: Select queue '${name}'.`)
		TL.show(name, () => {
			slide_hide();
			update_qsel_ctrls(ready);
		});
	};
	if (editor_status_check(['UNSAVED']) && confirm) {
		diags.DIALOG_SLIDE_UNSAVED(
			(status, val) => {
				if (status) { callback(); }
			}
		).show();
	} else { callback(); }
}

function queue_create() {
	/*
	*  Create a new queue and select it. If the current slide in
	*  the editor is unsaved, the user is asked for confirmation
	*  first.
	*/
	var callback = () => {
		dialog.dialog(
			dialog.TYPE.PROMPT,
			'Create queue',
			'Queue name',
			(status, val) => {
				if (!status) { return; }
				API.call(
					API.ENDP.QUEUE_CREATE,
					{'name': val},
					(data) => {
						if (API.handle_disp_error(data['error'])) {
							return;
						}
						// Select the new queue.
						update_qsel(false, () => {
							QUEUE_SELECT.val(val);
							queue_select(val, false, null);
						});
						console.log(
							`LibreSignage: Created queue '${val}'.`
						);
					}
				);
			},
			[
				new val.StrValidator({
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
					max: API.SERVER_LIMITS.QUEUE_NAME_MAX_LEN,
					regex: null
				}, "The queue name is too long."),
				new val.BlacklistValidator({
					bl: qsel_queues
				}, "This queue already exists.")
			]
		);
	};

	if (editor_status_check(['UNSAVED'])) {
		diags.DIALOG_SLIDE_UNSAVED(
			(status, val) => {
				if (status) { callback(); }
			}
		).show();
	} else {
		callback();
	}
}

function queue_remove() {
	/*
	*  Remove the selected queue. If the current slide in the
	*  editor is unsaved, the user is asked for confirmation
	*  first.
	*/
	let callback = () => {
		dialog.dialog(
			dialog.TYPE.CONFIRM,
			'Delete queue',
			'Delete the selected queue and all the slides in it?',
			(status) => {
				if (!status) { return; }
				API.call(
					API.ENDP.QUEUE_REMOVE,
					{'name': TL.queue.name},
					(data) => {
						if (API.handle_disp_error(data['error'])) {
							return;
						}
						update_qsel(true, null);
						slide_hide();
					}
				);
			},
			null
		);
	};
	if (editor_status_check(['UNSAVED'])) {
		diags.DIALOG_SLIDE_UNSAVED(
			(status, val) => {
				if (status) { callback(); }
			}
		).show();
	} else { callback(); }
}

function queue_view() {
	window.open(`/app/?q=${TL.queue.name}`);
}

function update_qsel(show_initial, ready) {
	/*
	*  Update the queue selector options.
	*/
	queue.get_list(API, (queues) => {
		queues.sort();
		qsel_queues = queues;

		QUEUE_SELECT.html('');
		for (let q of queues) {
			QUEUE_SELECT.append(`<option value="${q}">${q}</option>`);
		}
		if (show_initial && queues.length) {
			queue_select(queues[0], false, ready);
		} else {
			queue_select(null, false, ready);
		}
	});
}

function update_qsel_ctrls(ready) {
	// Update queue selector controls.
	QSEL_UI_DEFS.all(
		function() {
			this.state({
				'o': TL.queue && (TL.queue.owner == API.CONFIG.user.user)
			});
		},
		null,
		'button'
	);
	if (ready) { ready(); }
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
	SLIDE_SCHED.change(update_controls);

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
				{ bl: [API.CONFIG.user.user] },
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

	// Setup the queue selector event listener.
	QUEUE_SELECT.change(() => {
		queue_select(QUEUE_SELECT.val(), true, null);
	});
}

function setup() {
	util.setup_defaults();

	/*
	*  Add a listener for the 'beforeunload' event to make sure
	*  the user doesn't accidentally exit the page and lose changes.
	*/
	$(window).on('beforeunload', function(e) {
		if (editor_status_check(['NOSLIDE', 'SAVED'])) { return; }
		e.returnValue = "The selected slide is not saved. " +
				"Any changes will be lost if you exit " +
				"the page. Are you sure you want to " +
				"continue?";
		return e.returnValue;
	});

	// Setup inputs and other UI elements.
	inputs_setup(() => {
		update_controls();
		TL = new timeline.Timeline(API, slide_show);
		update_qsel(true);
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
				syn_err_id = syn_err_highlight(e.line(), e.line());
				MARKUP_ERR_DISPLAY.text('>> ' + e.toString(1));
			} else {
				syn_err_id = syn_err_clear(syn_err_id);
				MARKUP_ERR_DISPLAY.text('');
			}
		}
	);
}

$(document).ready(() => {
	API = new api.API(
		null,	// Use default config.
		setup
	);
});
