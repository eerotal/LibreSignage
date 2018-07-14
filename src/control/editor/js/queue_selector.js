const QUEUE_UI_DEFS = {
	'QUEUE_SELECT': new UIControl(
		_elem = QUEUE_SELECT,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, value) => { elem.val(value); },
		_clear = () => { elem.val(''); }
	),
	'QUEUE_CREATE': new UIControl(
		_elem = QUEUE_CREATE,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'QUEUE_VIEW': new UIControl(
		_elem = QUEUE_VIEW,
		_perm = (d) => { return true; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
	'QUEUE_REMOVE': new UIControl(
		_elem = QUEUE_REMOVE,
		_perm = (d) => { return d['o']; },
		_enabler = (elem, s) => { elem.prop('disabled', !s); },
		_mod = null,
		_getter = null,
		_setter = null,
		_clear = null
	),
}

function queue_create() {
	/*
	*  Create a new queue and select it.
	*/
	dialog(
		DIALOG.PROMPT,
		'Create queue',
		'Queue name',
		(status, val) => {
			if (!status) { return; }
			api_call(
				API_ENDP.QUEUE_CREATE,
				{'name': val},
				(data) => {
					var err = api_handle_disp_error(
						data['error']
					);
					if (err) { return; }

					// Select the new queue.
					update_qsel(false, () => {
						QUEUE_SELECT.val(val);
						timeline_show(val);
					});
					console.log(
						`LibreSignage: Created` +
						`queue '${val}'.`
					);
				}
			);
		},
		[
			new StrValidator({
				min: null,
				max: null,
				regex: /^[A-Za-z0-9_-]*$/
			}, "Invalid characters in queue name."),
			new StrValidator({
				min: 1,
				max: null,
				regex: null,
			}, "The queue name is too short."),
			new StrValidator({
				min: null,
				max: SERVER_LIMITS.QUEUE_NAME_MAX_LEN,
				regex: null
			}, "The queue name is too long.")
		]
	);
}

function queue_remove() {
	/*
	*  Remove the selected queue.
	*/
	dialog(
		DIALOG.CONFIRM,
		'Delete queue',
		'Delete the selected queue and all the slides in it?',
		(status) => {
			if (!status) { return; }
			api_call(
				API_ENDP.QUEUE_REMOVE,
				{'name': timeline_queue.name},
				(data) => {
					var err = api_handle_disp_error(
						data['error']
					);
					if (err) { return; }
					update_qsel(true);
				}
			);
		},
		null
	);
}

function queue_view() {
	window.open('/app/?q=' + timeline_queue.name);
}

function update_qsel(show_initial, ready) {
	/*
	*  Update the queue selector options.
	*/
	queue_get_list((queues) => {
		queues.sort();
		QUEUE_SELECT.html('');
		for (let q of queues) {
			QUEUE_SELECT.append(
				`<option value="${q}">${q}</option>`
			);
		}

		// Enable/disable buttons.
		if (queues.length) {
			QUEUE_REMOVE.prop('disabled', false);
			QUEUE_VIEW.prop('disabled', false);
		} else {
			QUEUE_REMOVE.prop('disabled', true);
			QUEUE_VIEW.prop('disabled', true);
		}

		// Select the first queue.
		if (show_initial && queues.length) {
			timeline_show(queues[0]);
		} else if (show_initial) {
			timeline_show(null);
		}

		// Update queue controls.
		if (!timeline_queue) {
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

			QUEUE_UI_DEFS[key].state({
				'o': timeline_queue.owner
					== API_CONFIG.user
			});
		}

		if (ready) { ready(); }
	});
}

function queue_setup() {
	// Handle queue selection.
	QUEUE_SELECT.change(() => {
		console.log("LibreSignage: Change timeline.");
		timeline_show(QUEUE_SELECT.val());
		update_qsel();
	});
	timeline_setup();
	update_qsel(true);
}
