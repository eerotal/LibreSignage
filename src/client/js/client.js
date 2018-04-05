var LOG = $("#log");

function log(msg) { LOG[0].innerHTML += msg; }

function main() {
	// Login using the authentication key defined in client.html.
	api_call(API_ENDP.AUTH_LOGIN_KEY, {key: KEY}, (resp) => {
		if (resp.error != API_E.API_E_OK) {
			log(
				`<span style="color: red;">
					Key authentication failed.
				</span>`
			);
			return;
		}
		log("Ready. Redirecting...<br>");

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
