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
	api_call(API_ENDP.AUTH_LOGIN_KEY, {key: KEY}, (resp) => {
		if (resp.error != API_E.API_E_OK) {
			log("Key authentication failed.", "red");
			return;
		}
		log("Ready. Redirecting...<br>", "green");

		// Redirect to display.
		window.location.replace(`${PROTOCOL}${HOST}/app`);
	});
}


function setup() {
	log("LibreSignage client setup.<br>");

	// Load necessary JavaScript libraries from HOST.
	$.getScript(
		`${PROTOCOL}${HOST}/common/js/api.js`,
		function(data, status, xhr) {
			log("Libs loaded.<br>");
			api_init(
				{
					hostname: HOST,
					protocol: PROTOCOL
				},
				main
			);
		}
	);
}

$(document).ready(setup);
