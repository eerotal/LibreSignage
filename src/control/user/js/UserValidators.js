var ValidatorController = require('libresignage/ui/validator/ValidatorController');
var ValidatorSelector = require('libresignage/ui/validator/ValidatorSelector');
var EqValidator = require('libresignage/ui/validator/EqValidator');
var StrValidator = require('libresignage/ui/validator/StrValidator');

/**
* Validators for the User Settings page.
*/
class UserValidators extends ValidatorController {
	constructor() {
		super({
			password: new ValidatorSelector(
				document.querySelector('#user-pass'),
				document.querySelector('#user-pass-confirm-group'),
				[
					new StrValidator(
						{ min: 1, max: null, regex: null },
						"",
						true
					)
				]
			),
			password_confirm: new ValidatorSelector(
				document.querySelector('#user-pass-confirm'),
				document.querySelector('#user-pass-confirm-group'),
				[
					new EqValidator(
						{
							value: () => {
								return document
									.querySelector('#user-pass')
									.value;
							}
						},
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
		document.querySelector('#user-pass').addEventListener(
			'input',
			() => {
				document
					.querySelector('#user-pass-confirm')
					.dispatchEvent(new Event('input'));
			}
		);
	}
}
module.exports = UserValidators;
