var LOG = $("#log");

function client_log(msg, color) {
	// Log a message with an optional color.
	var tmp = "";
	if (color) { tmp += `<span style="color: ${color}">`; }
	tmp += msg;
	if (color) { tmp += "</span>"; }
	LOG[0].innerHTML += tmp;
}

function client_main() {
	/*
	*  Login using the configuration values
	*  defined in client.html.
	*/
	api_login(
		USER,
		PASS,
		(resp) => {
			if (resp.error != API_E.API_E_OK) {
				client_log(
					"Login failed.",
					"red"
				);
				return;
			}
			client_log(
				"Ready. Redirecting...<br>",
				"green"
			);

			// Redirect to display.
			window.location.replace(
				`${PROTOCOL}//${HOST}/app` +
				`?tok=${resp.session.token}`
			);
		}
	);
}

function client_setup() {
	api_init(
		{
			hostname: HOST,
			protocol: PROTOCOL
		},
		client_main
	);
}
