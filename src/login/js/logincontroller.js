/*
*  Controller object for the Login page.
*/

class LoginController {
	constructor(api) {
		this.api = api;
	}

	async login(username, password, permanent) {
		await this.api.login(username, password, permanent);
	}
}
exports.LoginController = LoginController;
