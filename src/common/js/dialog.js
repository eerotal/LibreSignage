var DIALOG_TEMPLATE = `
<div id="dialog-overlay" style="display: none;">
	<div id="dialog" class="container alert alert-info">
		<h3 id="dialog-header" class="display-5"></h3>
		<p id="dialog-text">
		<div id="dialog-interaction" class="d-flex flex-row-reverse w-100 mt-3">
		</div>
	</div>
</div>
`;

var DIALOG = {
	ALERT: 0,
	CONFIRM: 1,
	PROMPT: 2
}

class Dialog {
	constructor(type, header, text, callback, validators) {
		this.type = type;
		this.header = header;
		this.text = text;
		this.callback = callback;
		this.validators = validators;
	}

	dialog_callback(obj, status, val) {
		/*
		*  Dialog callback function. This function can't have
		*  any references to 'this' since it is called from
		*  an event handler function. The object to whom this
		*  function belongs to is passed in the 'obj' argument
		*  instead.
		*/

		// Remove the dialog DOM elements.
		$("body #dialog-overlay").remove();

		// Remove validator trigger.
		this.val_trig = null;

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

	alert() {
		// Create alert HTML.
		var ok = $(
			"<button>", {
				class: 'btn btn-primary btn-dialog',
				id: 'dialog-btn-ok',
				text: 'Ok'
			}
		);
		ok.on(
			'click',
			{ obj: this },
			function(event) {
				event.data.obj.dialog_callback(
					event.data.obj,
					"ok",
					null
				);
			}
		);
		$("body #dialog-interaction").append(ok);
	}

	confirm() {
		// Create confirm HTML.
		var ok = $(
			"<button>", {
				class: 'btn btn-primary btn-dialog',
				id: 'dialog-btn-ok',
				text: 'Ok'
			}
		);
		ok.on(
			'click',
			{ obj: this },
			function(event) {
				event.data.obj.dialog_callback(
					event.data.obj,
					"ok",
					null
				);
			}
		);

		var cancel = $(
			"<button>", {
				class: 'btn btn-primary btn-dialog',
				id: 'dialog-btn-cancel',
				text: 'Cancel'
			}
		);
		cancel.on(
			'click',
			{ obj: this },
			function(event) {
				event.data.obj.dialog_callback(
					event.data.obj,
					"cancel",
					null
				);
			}
		);
		$("body #dialog-interaction").append(cancel);
		$("body #dialog-interaction").append(ok);
	}

	prompt() {
		// Create prompt HTML.
		var input_grp = $(
			'<div>', { id: 'dialog-input-grp' }
		);
		input_grp.append($(
			'<input>', {
				class: 'form-control mb-2',
				id: 'dialog-input'
			}
		));
		input_grp.append($(
			'<div>', { class: 'invalid-feedback my-0' }
		));

		var ok = $(
			'<button>', {
				class: 'btn btn-primary btn-dialog',
				id: 'dialog-btn-ok',
				text: 'Ok'
			}
		);
		ok.on(
			'click',
			{obj: this },
			function(event) {
				event.data.obj.dialog_callback(
					event.data.obj,
					"ok",
					$("body #dialog-input").val()
				);
			}
		);

		var cancel = $(
			"<button>", {
				class: 'btn btn-primary btn-dialog',
				id: 'dialog-btn-cancel',
				text: 'Cancel'
			}
		);
		cancel.on(
			'click',
			{ obj: this },
			function(event) {
				event.data.obj.dialog_callback(
					event.data.obj,
					"cancel",
					null
				);
			}
		);

		$("body #dialog-interaction").before(input_grp);
		$("body #dialog-interaction").append(cancel);
		$("body #dialog-interaction").append(ok);

		// Setup input validators.
		if (this.validators) {
			this.val_trig = new ValidatorTrigger(
				[new ValidatorSelector(
					$('#dialog-input'),
					$('#dialog-input-grp'),
					this.validators
				)],
				(valid) => {
					$('#dialog-btn-ok').prop(
						'disabled',
						!valid
					);
				}
			);
		}

	}

	_create() {
		/*
		*  Create the HTML DOM elements required for the
		*  different dialog types and add them to the
		*  body element.
		*/

		if (!$.inArray(this.type, DIALOG)) {
			throw new Error(
				"Unknown dialog type!"
			);
		}

		var html_dom = $.parseHTML(DIALOG_TEMPLATE);
		$("body").append(html_dom);

		// Setup header and text.
		$("body #dialog-header").text(this.header);
		$("body #dialog-text").text(this.text);

		// Run type specific setup functions.
		switch(this.type) {
			case DIALOG.ALERT:
				this.alert();
				break;
			case DIALOG.CONFIRM:
				this.confirm();
				break;
			case DIALOG.PROMPT:
				this.prompt();
				break;
			default:
				break;
		}
		return true;
	}

	show() {
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
			document.activeElement.blur();
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

function dialog(type, header, text, callback, validators) {
	return new Dialog(
		type,
		header,
		text,
		callback,
		validators
	).show();
}
