/*
*  View class for the LibreSignage control panel.
*/

var $ = require('jquery');
var ControlPanelController = require('./controlpanelcontroller.js').ControlPanelController;

const quota_bar = (name, val, min, max) => `
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

class ControlPanelView {
	constructor(api) {
		this.controller = new ControlPanelController(api);
		this.populate_ui();
	}

	populate_ui() {
		/*
		*  Populate the control panel quota bars.
		*/
		let html = '';
		let quota = this.controller.get_quota();

		for (let k of quota.get_keys()) {
			html += quota_bar(
				quota.get_description(k),
				quota.get_used(k),
				0,
				quota.get_limit(k)
			);
		}
		$('#user-quota-cont').html(html);
	}
}
exports.ControlPanelView = ControlPanelView;
