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
					update_queue_selector(false);
					QUEUE_SELECT.val(val);
					timeline_show(val);

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

					update_queue_selector(true);
				}
			);
		},
		null
	);
}

function queue_view() {
	window.open('/app/?q=' + timeline_queue.name);
}

function update_queue_selector(show_initial) {
	/*
	*  Update the queue selector options.
	*/
	api_call(API_ENDP.QUEUE_LIST, {}, (data) => {
		if (api_handle_disp_error(data.error)) {
			return;
		}

		QUEUE_SELECT.html('');
		for (var k in data['queues']) {
			QUEUE_SELECT.append(
				`<option value="${data['queues'][k]}">` +
				`${data['queues'][k]}</option>`
			);
		}

		// Enable/disable buttons.
		if (data['queues'].length) {
			QUEUE_REMOVE.prop('disabled', false);
			QUEUE_VIEW.prop('disabled', false);
		} else {
			QUEUE_REMOVE.prop('disabled', true);
			QUEUE_VIEW.prop('disabled', true);
		}

		// Select the first queue.
		if (show_initial && data['queues'].length) {
			timeline_show(data['queues'][0]);
		} else if (show_initial) {
			timeline_show(null);
		}
	});
}

function queue_setup() {
	// Handle queue selection.
	QUEUE_SELECT.change(() => {
		console.log("LibreSignage: Change timeline.");
		timeline_show(QUEUE_SELECT.val());
	});

	update_queue_selector(true);
	timeline_setup();
}
