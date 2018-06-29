function queue_create() {
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
					err = api_handle_disp_error(
						data['error']
					);
					if (err) { return; }

					update_queue_selector(false);
					QUEUE_SELECT.val(val);
					timeline_show(val);

					console.log(
						`LibreSignage: Created` +
						`queue '${val}'.`
					);
				}
			);
		}
	);
}

function queue_remove() {
	// TODO
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

		if (show_initial && data['queues'].length) {
			timeline_show(data['queues'][0]);
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
