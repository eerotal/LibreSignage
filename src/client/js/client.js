var LOG = $("#log");

function log(msg, color) {
	// Log a message with an optional color.
	var tmp = "";
	if (color) { tmp += `<span style="color: ${color}">`; }
	tmp += msg;
	if (color) { tmp += "</span>"; }
	LOG[0].innerHTML += tmp;
}

function main() {
	// Login using the authentication key defined in client.html.
	api_login(
		USER,
		PASS,
		(resp) => {
			if (resp.error != API_E.API_E_OK) {
				log("Login failed.", "red");
				return;
			}
			log("Ready. Redirecting...<br>", "green");

			// Redirect to display.
			window.location.replace(
				`${PROTOCOL}//${HOST}/app` +
				`?tok=${resp.session.token}`
			);
		}
	);
}

log("LibreSignage display client startup.<br>");
$.when(
	$.getScript(`${PROTOCOL}//${HOST}/common/js/api.js`),
	$.getScript(`${PROTOCOL}//${HOST}/common/js/cookie.js`),
	$.Deferred(function (deferred) {
		$( deferred.resolve );
	})
).done(function() {
	log("Libs loaded.<br>");
	api_init(
		{
			hostname: HOST,
			protocol: PROTOCOL
		},
		main
	);
});
