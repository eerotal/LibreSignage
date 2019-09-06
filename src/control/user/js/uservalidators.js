var $ = require('jquery');

var ValidatorController = require('libresignage/ui/validator/ValidatorController');
var ValidatorSelector = require('libresignage/ui/validator/ValidatorSelector');
var EqValidator = require('libresignage/ui/validator/EqValidator');
var StrValidator = require('libresignage/ui/validator/StrValidator');

class UserValidators extends ValidatorController {
	constructor() {
		super({
			password: new ValidatorSelector(
				$('#user-pass')[0],
				$('#user-pass-confirm-group')[0],
				[
					new StrValidator(
						{ min: 1, max: null, regex: null },
						"",
						true
					)
				]
			),
			password_confirm: new ValidatorSelector(
				$('#user-pass-confirm')[0],
				$('#user-pass-confirm-group')[0],
				[
					new EqValidator(
						{ value: () => $('#user-pass').val() },
						"The passwords don't match."
					)
				]
			)
		});

		/*
		*  Make password equality validation work properly by firing
		*  an input event on #user-pass-confirm every time an input
		*  event is fired on #user-pass. This makes sure the inputs
		*  are validated when either input is modified.
		*
		*  Note that adding validators that cross-check the inputs'
		*  values won't work because in that case editing one of the
		*  inputs won't make the validation state update for the other
		*  one. This messes up any ValidatorTriggers attached onto the
		*  validators.
		*/
		$('#user-pass').on(
			'input',
			() => $('#user-pass-confirm').trigger('input')
		);
	}
}
exports.UserValidators = UserValidators;
