var APIEndpoints = require('libresignage/api/APIEndpoints');

class UserController {
	constructor(api) {
		this.api = api;
	}

	async save_password(password) {
		/*
		*  Change the password of the current user to 'password'.
		*/
		let user = this.api.get_session().get_user();
		user.set_password(password);
		await user.save();
	}

	async logout_other_sessions() {
		/*
		*  Logout all other sessions except the current one.
		*/
		await this.api.call(APIEndpoints.AUTH_LOGOUT_OTHER, null);
	}

	get_user() { return this.api.get_session().get_user(); }
}
exports.UserController = UserController;
