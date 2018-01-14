var DIALOG_TEMPLATE =
	'<div id="dialog-overlay" style="display: none;">' +
	'	<div id="dialog" class="container alert alert-info">' +
	'		<h3 id="dialog-header" class="display-5"></h3>' +
	'		<p id="dialog-text">' +
	'		<div id="dialog-interaction" class="d-flex flex-row-reverse w-100">' +
	'		</div>' +
	'	</div>' +
	'</div>';

var DIALOG = {
	ALERT: 0,
	CONFIRM: 1,
	PROMPT: 2
}

function Dialog(type, header, text, callback) {
	this.type = type;
	this.header = header;
	this.text = text;
	this.callback = callback;

	this._dialog_callback = function(obj, status, val) {
		/*
		*  Dialog callback function. This function can't have
		*  any references to 'this' since it is called from
		*  an event handler function. The object to whom this
		*  function belongs to is passed in the 'obj' argument
		*  instead.
		*/
		$("body #dialog-overlay").remove();
		if (obj.callback) {
			switch(status) {
				case "ok":
					obj.callback(true, val);
					break;
				default:
					obj.callback(false, null);
					break;
			}
		}
	}

	this._alert_create = function() {
		/*
		*  Create the alert dialog specific HTML DOM elements.
		*/
		var ok = $("<button>", {
			class: 'btn btn-primary btn-dialog',
			id: 'dialog-btn-ok',
			text: 'Ok'
		});
		ok.on('click', { obj: this }, function(event) {
			event.data.obj._dialog_callback(event.data.obj,
							"ok", null);
		});
		$("body #dialog-interaction").append(ok);
	}

	this._confirm_create = function() {
		/*
		*  Create the confirm dialog specific HTML DOM elements.
		*/

		var tmp_cb = this._dialog_callback;
		var ok = $("<button>", {
			class: 'btn btn-primary btn-dialog',
			id: 'dialog-btn-ok',
			text: 'Ok'
		});
		ok.on('click', { obj: this }, function(event) {
			event.data.obj._dialog_callback(event.data.obj,
							"ok", null);
		});

		var cancel = $("<button>", {
			class: 'btn btn-primary btn-dialog',
			id: 'dialog-btn-cancel',
			text: 'Cancel'
		});
		cancel.on('click', { obj: this }, function(event) {
			event.data.obj._dialog_callback(event.data.obj,
							"cancel", null);
		});

		$("body #dialog-interaction").append(cancel);
		$("body #dialog-interaction").append(ok);
	}

	this._prompt_create = function() {
		/*
		*  Create the prompt dialog specific HTML DOM elements.
		*/
		var tmp_cb = this._dialog_callback;

		var input = $("<input>", {
			class: 'form-control',
			id: 'dialog-input'
		});

		var ok = $("<button>", {
			class: 'btn btn-primary btn-dialog',
			id: 'dialog-btn-ok',
			text: 'Ok'
		});
		ok.on('click', {obj: this }, function(event) {
			var val = $("body #dialog-input").val();
			event.data.obj._dialog_callback(event.data.obj,
							"ok", val);
		});

		var cancel = $("<button>", {
			class: 'btn btn-primary btn-dialog',
			id: 'dialog-btn-cancel',
			text: 'Cancel'
		});
		cancel.on('click', { obj: this }, function(event) {
			event.data.obj._dialog_callback(event.data.obj,
							"cancel", null);
		});

		$("body #dialog-interaction").before(input);
		$("body #dialog-interaction").append(cancel);
		$("body #dialog-interaction").append(ok);
	}

	this._create = function() {
		/*
		*  Create the HTML DOM elements required for the
		*  different dialog types and add them to the
		*  body element.
		*/

		if (!$.inArray(this.type, DIALOG)) {
			console.error("LibreSignage: Unknown dialog " +
					"type!");
			return false;
		}

		var html_dom = $.parseHTML(DIALOG_TEMPLATE);
		$("body").append(html_dom);

		// Setup header and text.
		$("body #dialog-header").text(this.header);
		$("body #dialog-text").text(this.text);

		// Run type specific setup functions.
		switch(this.type) {
			case DIALOG.ALERT:
				this._alert_create();
				break;
			case DIALOG.CONFIRM:
				this._confirm_create();
				break;
			case DIALOG.PROMPT:
				this._prompt_create();
				break;
			default:
				break;
		}
		return true;
	}

	this.show = function() {
		/*
		*  Show this dialog. Returns true on success and
		*  false on failure.
		*
		*  User interaction while there's a dialog visible
		*  is prevented by autofocusing on the dialog OK button,
		*  preventing the use of the tab key and displaying
		*  the overlay div that catches all mouse click events.
		*/

		if (this._create()) {
			$("body #dialog-overlay").show();
			$(document).keydown(function(event) {
				if (event.keyCode == 9) {
					event.preventDefault();
				}
			});
			$("body #dialog-btn-ok").focus();
			return true;
		}
		return false;
	}
}

function dialog(type, header, text, callback) {
	/*
	*  A convenience function for creating dialogs.
	*/

	var dialog = new Dialog(type, header, text, callback);
	return dialog.show();
}
