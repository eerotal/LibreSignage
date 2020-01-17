var ControlPanelController = require('./ControlPanelController.js');

/**
* View class for the Control Panel.
*/
class ControlPanelView {
	/**
	* Construct a new ControlPanelView object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.controller = new ControlPanelController(api);
		this.populate_ui();
	}

	/**
	* Create a quota bar DOM node.
	*
	* @param {string} name The name of the quota.
	* @param {number} val  The amount of quota used.
	* @param {number} min  The minimum quota value.
	* @param {number} max  The maximum quota value.
	*/
	static make_quota_bar_node(name, val, min, max) {
		let div = document.createElement('DIV');
		div.innerHTML = `
			<h6>${name}</h6>
			<div class="row quota-bar">
				<div class="p-0 pr-2 m-0 col-2 text-right">
					${val}/${max}
				</div>
				<div class="col-10 progress quota-bar">
					<div class="progress-bar bg-success
							progress-bar-striped"
						role="progressbar"
						aria-valuenow="${val}"
						style="width: ${(val/max)*100}%;"
						aria-valuemin="${min}"
						aria-valuemax="${max}">
					</div>
				</div>
			</div>
		`;
		return div;
	}

	/**
	* Populate the control panel quota bars.
	*/
	populate_ui() {
		let quota = this.controller.get_quota();
		for (let k of quota.get_keys()) {
			document.querySelector('#user-quota-cont').appendChild(
				ControlPanelView.make_quota_bar_node(
					quota.get_description(k),
					quota.get_used(k),
					0,
					quota.get_limit(k)
				)
			);
		}
	}
}
module.exports = ControlPanelView;
