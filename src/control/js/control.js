QUOTA_CONTAINER = $("#user-quota-cont");

const quota_bar = (name, val, min, max) => `
	<h6>${name}</h6>

	<div class="row quota-bar">
		<div class="p-0 m-0 col-2">${val}/${max}</div>
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
	api_call(API_ENDP.USER_GET_QUOTA, null, (resp) => {
		if (resp.error) {
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


ctrl_setup();
