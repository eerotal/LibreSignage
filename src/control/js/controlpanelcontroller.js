/*
*  Controller class for the LibreSignage control panel.
*/

class ControlPanelController {
	constructor(api) {
		this.api = api;
	}

	get_quota() {
		/*
		*  Get the quota of the logged in user.
		*/
		return this.api.get_session().get_user().get_quota();
	}
}
exports.ControlPanelController = ControlPanelController;
