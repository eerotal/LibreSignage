/**
* Controller class for ControlPanelView.
*/
class ControlPanelController {
	/**
	* Construct a new ControlPanelController.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
	}

	/**
	* Get the quota values of the logged in user.
	*/
	get_quota() {
		return this.api.get_session().get_user().get_quota();
	}
}
module.exports = ControlPanelController;
