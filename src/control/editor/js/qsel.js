var $ = require('jquery');

var uic = require('ls-uicontrol');
var val = require('ls-validator');
var dialog = require('ls-dialog');
var queue = require('ls-queue');

var API = null;
var TL = null;

const QUEUE_SELECT		= $("#queue-select");
const QUEUE_CREATE		= $("#queue-create");
const QUEUE_VIEW		= $("#queue-view");
const QUEUE_REMOVE		= $("#queue-remove");

const QUEUE_UI_DEFS = {
	'QUEUE_SELECT': new uic.UIInput(
		_elem = QUEUE_SELECT,
		_perm = () => { return true; },
		_enabler = null,
		_mod = null,
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, value) => { elem.val(value); },
		_clear = () => { elem.val(''); }
	),
	'QUEUE_CREATE': new uic.UIButton(
		_elem = QUEUE_CREATE,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'click': queue_create
		}
	),
	'QUEUE_VIEW': new uic.UIButton(
		_elem = QUEUE_VIEW,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'click': queue_view
		}
	),
	'QUEUE_REMOVE': new uic.UIButton(
		_elem = QUEUE_REMOVE,
		_perm = (d) => { return d['o'] && TL.queue.slides.length(); },
		_enabler = null,
		_attach = {
			'click': queue_remove
		}
	),
}

function queue_create() {
	/*
	*  Create a new queue and select it.
	*/
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
					if (API.handle_disp_error(data['error'])) { return; }
					// Select the new queue.
					update_qsel(false, () => {
						QUEUE_SELECT.val(val);
						TL.show(val);
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
			}, "The queue name is too long.")
		]
	);
}

function queue_remove() {
	/*
	*  Remove the selected queue.
	*/
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
					update_qsel(true);
				}
			);
		},
		null
	);
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
		QUEUE_SELECT.html('');
		for (let q of queues) {
			QUEUE_SELECT.append(`<option value="${q}">${q}</option>`);
		}

		// Select the first queue.
		if (show_initial && queues.length) {
			TL.show(queues[0]);
		} else if (show_initial) {
			TL.show(null);
		}

		// Update queue controls.
		if (!TL.queue) {
			for (let key of (Object.keys(QUEUE_UI_DEFS))) {
				if ([
					'QUEUE_SELECT',
					'QUEUE_CREATE'
				].includes(key)) { continue; }
				QUEUE_UI_DEFS[key].set_state(false);
			}
		}
		for (let key of Object.keys(QUEUE_UI_DEFS)) {
			if ([
				'QUEUE_SELECT',
				'QUEUE_CREATE'
			].includes(key)) { continue; }

			console.log(TL.queue);
			QUEUE_UI_DEFS[key].state({
				'o': TL.queue.owner == API.CONFIG.user
			});
		}
		if (ready) { ready(); }
	});
}

exports.setup = function(api, tl) {
	API = api;
	TL = tl;

	// Handle queue selection.
	QUEUE_SELECT.change(() => {
		console.log("LibreSignage: Change timeline.");
		TL.show(QUEUE_SELECT.val());
		update_qsel();
	});
	update_qsel(true);
}
