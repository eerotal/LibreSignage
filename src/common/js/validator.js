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

	attach(query) {
		/*
		*  Attach the validator the the elements selected by
		*  the jQuery object 'query' or if 'query' is a string,
		*  use it to create a jQuery object.
		*/
		var tmp_q = null;
		if (typeof query == 'string') {
			if (query.length) {
				tmp_q = $(query);
			} else {
				throw new Error('Invalid query string.');
			}
		} else {
			tmp_q = query;
		}

		if (!tmp_q.length) {
			return;
		}

		this.attached.push(tmp_q);
		$(query).on(
			'input',
			(event) => {
				if (this.enabled) {
					this._validate(this, event);
				}
			}
		);
	}

	detach() {
		/*
		*  Detach the validator from all elements.
		*/
		for (var i in this.attached) {
			this.attached[i].off('input', null, this._validate);
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
		*  the state occurs.
		*/
		if (!valid && this.valid) {
			for (var i in this.attached) {
				this.attached[i].addClass('is-invalid');
			}
			for (var i in this.callbacks) {
				this.callbacks[i](false);
			}
			this.valid = false;
		} else if (valid && !this.valid) {
			for (var i in this.attached) {
				this.attached[i].removeClass('is-invalid');
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
}

class NumValidator extends Validator {
	/*
	*  Make sure number inputs are in the range [min, max].
	*  Below is a list of the accepted settings.
	*    min = The minimum value.
	*    max = The maximum value.
	*/
	_validate(validator) {
		var q = null;
		var min = validator.settings.min;
		var max = validator.settings.max;

		for (var i in validator.attached) {
			q = validator.attached[i];
			if (q.val() < min || q.val() > max) {
				validator._set_valid_state(false);
			} else {
				validator._set_valid_state(true);
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
	*    max = The maximum string length (or -1 for unlimited).
	*    regex = A regex to match (or null).
	*/

	_check_conditions(validator, value) {
		var min = validator.settings.min;
		var max = validator.settings.max;
		var regex = validator.settings.regex;

		var a = value.length >= min;
		var b = max == -1 || value.length <= max;
		var c = !regex || value.match(regex);

		return a && b && c;
	}

	_validate(validator) {
		var q = null;
		for (var i in validator.attached) {
			q = validator.attached[i];
			if (!this._check_conditions(validator, q.val())) {
				validator._set_valid_state(false);
			} else {
				validator._set_valid_state(true);
			}
		}
	}
}
