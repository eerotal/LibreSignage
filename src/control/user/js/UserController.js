var APIEndpoints = require('libresignage/api/APIEndpoints');

/**
* Controller class for the UserView class.
*/
class UserController {
	/**
	* Construct a new UserController object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
	}

	/**
	* Change the password of the current user.
	*
	* @param {string} password The new password.
	*/
	async save_password(password) {
		let user = this.api.get_session().get_user();
		user.set_password(password);
		await user.save();
	}

	/**
	* Logout all other sessions except the current one.
	*/
	async logout_other_sessions() {
		await this.api.call(APIEndpoints.AUTH_LOGOUT_OTHER, null);
	}

	/**
	* Get the currently logged in user.
	*/
	get_user() { return this.api.get_session().get_user(); }
}
module.exports = UserController;
