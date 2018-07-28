var $ = require('jquery');
var api = require('ls-api');

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

function ctrl_setup() {
	API.call(API.ENDP.USER_GET_QUOTA, null, (resp) => {
		if (API.handle_disp_error(resp.error)) {
			throw new Error("API exception while loading " +
					"user quota.");
		}
		var tmp = "";
		for (var k in resp.quota) {
			tmp += quota_bar(resp.quota[k].disp,
					resp.quota[k].used,
					0,
					resp.quota[k].limit);
		}
		QUOTA_CONTAINER.html(tmp);
	});
}

$(document).ready(() => {
	API = new api.API(null, ctrl_setup);
});
