QUOTA_CONTAINER = $("#user-quota-cont");

const quota_bar = (name, val, min, max) => `
	<h6>${name}</h6>
	<div class="progress quota-bar">
		<div class="progress-bar bg-success progress-bar-striped"
			role="progressbar"
			aria-valuenow="${val}"
			style="width: ${(val/max)*100}%;"
			aria-valuemin="${min}"
			aria-valuemax="${max}">
		</div>
		<span class="w-100">
			${val}/${max}
		</span>
	</div>
`;

function ctrl_setup() {
	api_call(API_ENDP.USER_GET_QUOTA, null, (response) => {
		if (!response || response.error) {
			throw new Error("API exception while loading " +
					"user quota.");
		}
		var tmp = "";
		for (var k in response.quota) {
			tmp += quota_bar(response.quota[k].disp,
					response.quota[k].used,
					0,
					response.quota[k].limit);
		}
		QUOTA_CONTAINER.html(tmp);
	});
}


ctrl_setup();
