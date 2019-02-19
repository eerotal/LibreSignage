var $ = require('jquery');

var ValidatorController = require('ls-validator').ValidatorController;
var ValidatorSelector = require('ls-validator').ValidatorSelector;
var EqValidator = require('ls-validator').EqValidator;

class UserValidators extends ValidatorController {
	constructor() {
		super({
			password: new ValidatorSelector(
				$('#user-pass')[0],
				$('#user-pass-confirm-group')[0],
				[
					new EqValidator(
						{ value: () => $('#user-pass-confirm').val() },
						"The passwords don't match."
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
	}
}
exports.UserValidators = UserValidators;
