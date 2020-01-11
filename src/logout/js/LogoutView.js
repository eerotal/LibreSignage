var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var LogoutController = require('./LogoutController');

/**
* View object for the logout page.
*/
class LogoutView {
	/**
	* Construct a new LoginView object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.LOGOUT_REDIR_TIME = 2000;
		this.api = api;
		this.controller = new LogoutController(api);
	}

	/**
	* Initialize the view and log out the user.
	*/
	async init() {
		try {
			await this.controller.logout()
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}

		this.redirect();
	}

	/**
	* Redirect the user to the login page.
	*/
	redirect() {
		setTimeout(() => {
			window.location.href = "/login";
		}, this.LOGOUT_REDIR_TIME);
	}
}
module.exports = LogoutView;
