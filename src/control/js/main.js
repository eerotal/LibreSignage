var $ = require('jquery');
var APIInterface = require('ls-api').APIInterface;
var APIEndpoints = require('ls-api').APIEndpoints;
var APIUI = require('ls-api-ui');

var API = null;
const QUOTA_CONTAINER = $("#user-quota-cont");

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

async function ctrl_setup() {
	let reps = null;
	try {
		resp = await API.call(APIEndpoints.USER_GET_QUOTA, null);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let tmp = "";
	for (var k in resp.quota) {
		tmp += quota_bar(
			resp.quota[k].disp,
			resp.quota[k].used,
			0,
			resp.quota[k].limit
		);
	}
	QUOTA_CONTAINER.html(tmp);
}

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	await ctrl_setup();
});
