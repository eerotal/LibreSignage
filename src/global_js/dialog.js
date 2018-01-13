var _dialog_alert_callback = null;
var _dialog_confirm_callback = null;

function dialog_confirm(header, text, callback) {
	$("#dialog-confirm-header").html(header);
	$("#dialog-confirm-text").html(text);
	$("#dialog-confirm-overlay").show();
	_dialog_confirm_callback = callback;
}

function dialog_confirm_cancel() {
	if (_dialog_confirm_callback) {
		_dialog_confirm_callback(false);
		_dialog_confirm_callback = null;
	}
	$("#dialog-confirm-overlay").hide();
}

function dialog_confirm_ok() {
	if (_dialog_confirm_callback) {
		_dialog_confirm_callback(true);
		_dialog_confirm_callback = null;
	}
	$("#dialog-confirm-overlay").hide();
}

function dialog_alert(header, text, callback) {
	$("#dialog-alert-header").html(header);
	$("#dialog-alert-text").html(text);
	$("#dialog-alert-overlay").show();
	_dialog_alert_callback = callback;
}

function dialog_alert_ok() {
	if (_dialog_alert_callback) {
		_dialog_alert_callback();
		_dialog_alert_callback = null;
	}
	$("#dialog-alert-overlay").hide();
}
