/**
* Controller class for the logout page.
*/
class LogoutController {
	/**
	* Construct a new LogoutController.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
	}

	/**
	* Log out the current user via the API.
	*/
	async logout() {
		if (this.api.get_session() != null) {
			await this.api.logout();
		}
	}
}
module.exports = LogoutController;
