var CONTENT_INPUT = $('#content-input');
var _selected_screen = "";

function screen_show(screen) {
	console.log("LibreSignage: Show screen '" + screen + "'");
	_selected_screen = screen;

	api_call(API_ENDP.CONTENT_GET, {'id': screen}, function(response) {
		if (!response || response.error) {
			console.log("LibreSignage: API error!");
			CONTENT_INPUT.val('');
			return;
		}
		CONTENT_INPUT.val(response.html);
	});
}

function screen_rm() {
	if (!_selected_screen) {
		alert("Please select a screen to remove first.");
		return;
	}

	if (!confirm("Are you sure you want to delete the screen '" +
		_selected_screen + "'.")) {
		return;
	}

	api_call(API_ENDP.CONTENT_RM, {'id': _selected_screen},
						function(response) {
		if (!response || response.error) {
			console.log("LibreSignage: API error!");
			return;
		}

		$('#screen-btn-' + _selected_screen).remove();

		console.log("LibreSignage: Deleted screen '" +
				_selected_screen + "'.");
	});
}

function screen_mk() {
	console.log("LibreSignage: Create screen!");
}
