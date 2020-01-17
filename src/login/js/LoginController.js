/**
* Controller class for the Login page.
*/
class LoginController {
	/**
	* Construct a new LoginController object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
	}

	/**
	* Login via the LibreSignage API.
	*
	* @param {string}  username  The username to use.
	* @param {string}  password  The password to use.
	* @param {boolean} permanent Whether to create a permanent session.
	*/
	async login(username, password, permanent) {
		await this.api.login(username, password, permanent);
	}
}
module.exports = LoginController;
