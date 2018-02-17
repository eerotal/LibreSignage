/*
*  A universal input validator implementation for LibreSignage.
*/

class ValidatorGroup {
	constructor(validators, callback) {
		/*
		*  Constructor for the ValidatorGroup class.
		*  'validators' is an array of validator objects
		*  to use for the ValidatorGroup.
		*/
		if (!Array.isArray(validators)) {
			throw new Error('Invalid validators for ' +
					'ValidatorGroup.');
		}

		this.validators = validators;

		/*
		*  Wrap the calc_state() function so that
		*  'this' can be used in the function.
		*/
		this.calc_state_wrap = (valid) => {
			this.calc_state(valid);
		};

		this.valid_cnt = this.validators.length;
		for (var i in this.validators) {
			this.validators[i].add_callback(
				this.calc_state_wrap
			);

			// Calculate the initial state for this group.
			if (!this.validators[i].valid) {
				this.valid_cnt--;
			}
		}

		if (this.valid_cnt == this.validators.length) {
			this.valid = true;
		} else {
			this.valid = false;
		}

		this.callback = callback;
	}

	calc_state(valid) {
		/*
		*  This function is called every time a Validator
		*  in the this.validators array changes state.
		*/
		if (valid) {
			this.valid_cnt++;
		} else {
			this.valid_cnt--;
		}

		if (this.valid_cnt == this.validators.length) {
			if (!this.valid) {
				if (this.callback) {
					this.callback(true);
				}
				this.valid = true;
			}
		} else {
			if (this.valid) {
				if (this.callback) {
					this.callback(false);
				}
				this.valid = false;
			}
		}
	}

	for_each(func) {
		for (var i in this.validators) {
			func(this.validators[i]);
		}
	}
}

class Validator {
	constructor(settings, callbacks) {
		/*
		*  Constructor function for the Validator class.
		*  'settings' should contain the validator specific
		*  settings as a dictionary and 'callbacks' is an
		*  array of callback functions to call when a change
		*  in the validation state occurs.
		*/

		if (settings && settings != Object(settings)) {
			throw new Error('Invalid validator settings.');
		}
		if (callbacks && !Array.isArray(callbacks)) {
			throw new Error('Invalid validator callbacks');
		}

		this.settings = settings;
		this.callbacks = callbacks;

		this.attached = [];
		this.valid = true;
		this.enabled = true;
	}

	attach(query, style_on) {
		/*
		*  Attach the validator to the elements selected by
		*  the jQuery object 'query' or if 'query' is a string,
		*  use it to create a jQuery object. If 'style_on' is not
		*  null, the styling is applied to the elements selected
		*  by the jQuery object or the jQuery object created using
		*  the query string in 'style_on'. Otherwise styling is
		*  applied to the elements selected using 'query'.
		*/

		var tmp_query = null;
		var tmp_style_on = null;

		// Get the 'query' jQuery object.
		if (typeof query == 'string') {
			if (query.length) {
				tmp_query = $(query);
			} else {
				throw new Error('query invalid.');
			}
		} else {
			tmp_query = query;
		}

		// Get the 'style_on' jQuery object.
		if (typeof style_on == 'string') {
			if (query.length) {
				tmp_style_on = $(style_on);
			} else {
				throw new Error('style_on invalid.');
			}
		} else if (style_on == null) {
			tmp_style_on = tmp_query;
		} else {
			tmp_style_on = style_on;
		}

		// Attach event handler.
		tmp_query.on(
			'input',
			(event) => {
				if (this.enabled) {
					this._validate(this, event);
				}
			}
		);

		this.attached[this.attached.length] = {
			'event_from': tmp_query,
			'style_on': tmp_style_on
		}
	}

	detach() {
		/*
		*  Detach the validator from all elements.
		*/

		// Reset styling.
		this._set_valid_state(true);

		// Detach event handlers.
		for (var i in this.attached) {
			this.attached[i]['event_from'].off(
				'input',
				null,
				this._validate
			);
		}
		this.attached = [];
	}

	add_callback(cb) {
		if (!this.callbacks) {
			this.callbacks = [];
		}
		this.callbacks.push(cb);
	}

	remove_callback() {
		var i = this.callbacks.indexOf(cb);
		if (i != -1) {
			this.callbacks.splice(i, 1);
		}
	}

	enable() {
		// Enable and validate the Validator.
		this.enabled = true;
		this._validate(this);
	}

	disable() {
		// Disable and override the Validator.
		this.enabled = false;
		this._set_valid_state(true);
	}

	_set_valid_state(valid) {
		/*
		*  Set the state of this Validator. The assigned
		*  callback functions are called if a change in
		*  the state occurs. This function also applies
		*  the CSS styling to the selected parent 'style_on'
		*  elements and their descendants recursively.
		*/
		var q = 'input, selector, textarea';
		var att = this.attached;
		var e = [];

		// Select the correct elements.
		for (var i in att) {
			if (att[i]['style_on'].is(q)) {
				e.push(att[i]['style_on']);
			}
			e.push(att[i]['style_on'].find($(q)));
		}

		// Apply styling, call callbacks and set state.
		if (!valid && this.valid) {
			for (var i in e) {
				e[i].addClass('is-invalid');
			}
			for (var i in this.callbacks) {
				this.callbacks[i](false);
			}
			this.valid = false;
		} else if (valid && !this.valid) {
			for (var i in e) {
				e[i].removeClass('is-invalid');
			}
			for (var i in this.callbacks) {
				this.callbacks[i](true);
			}
			this.valid = true;
		}
	}

	_validate() {
		throw new Error('Subclasses should implement the ' +
				'validator _validate() function.');
	}

	_chk_settings(settings) {
		/*
		*  Check the supplied settings against the array
		*  of accepted settings and throw an error if
		*  required settings don't exist.
		*/
		if (!Array.isArray(settings)) {
			throw new Error('Invalid type for "settings".');
		}
		for (var i in settings) {
			var skeys = Object.keys(this.settings);
			if (skeys.indexOf(settings[i]) == -1) {
				throw new Error('Invalid ' +
					'Validator settings. ("' +
					settings[i] + '" missing)');
			}
		}
	}
}

class NumValidator extends Validator {
	/*
	*  Make sure number inputs are in the range [min, max].
	*  Below is a list of the accepted settings.
	*    min = The minimum value or null for unlimited.
	*    max = The maximum value or null for unlimited.
	*/
	constructor(...args) {
		super(...args);
		this._chk_settings(['min', 'max']);
	}

	_numval_chk(value) {
		var min = this.settings.min;
		var max = this.settings.max;

		var a = (min == null || value >= min);
		var b = (max == null || value <= max);

		return a && b;
	}

	_validate() {
		var val = null;
		for (var i in this.attached) {
			val = this.attached[i]['event_from'].val();
			if (this._numval_chk(val)) {
				this._set_valid_state(true);
			} else {
				this._set_valid_state(false);
			}
		}
	}
}

class StrValidator extends Validator {
	/*
	*  Make sure the length of a text input's text
	*  is within the range [min, max]. Below is a list
	*  of the accepted settings.
	*    min = The minimum string length.
	*    max = The maximum string length (or null for unlimited).
	*    regex = A regex to match (or null if unused).
	*/

	constructor(...args) {
		super(...args);
		this._chk_settings(['min', 'max', 'regex']);
	}

	_strval_chk(value) {
		var min = this.settings.min;
		var max = this.settings.max;
		var regex = this.settings.regex;

		var a = (value.length >= min);
		var b = (max == null || value.length <= max);
		var c = (regex == null || value.match(regex));

		return a && b && c;
	}

	_validate() {
		var val = null;
		for (var i in this.attached) {
			val = this.attached[i]['event_from'].val();
			if (!this._strval_chk(val)) {
				this._set_valid_state(false);
			} else {
				this._set_valid_state(true);
			}
		}
	}
}
