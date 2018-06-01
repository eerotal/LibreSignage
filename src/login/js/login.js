var LOGIN_LANDING = "/control";
var INPUT_USERNAME = $("#input-user");
var INPUT_PASSWORD = $("#input-pass");
var BTN_LOGIN = $("#btn-login");

function login_redirect(uri) {
	window.location.assign(uri);
}

function login() {
	api_login(
		INPUT_USERNAME.val(),
		INPUT_PASSWORD.val(),
		false,
		(resp) => {
			if (resp.error == API_E.API_E_INCORRECT_CREDS) {
				login_redirect("/login?failed=1");
			} else if (resp.error == API_E.API_E_OK) {
				login_redirect(LOGIN_LANDING);
			} else {
				api_handle_disp_error(resp.error);
			}
		}
	)
}

function login_setup() {
	INPUT_USERNAME[0].addEventListener('keyup', (event) => {
		if (event.keyCode == 13) { // Enter
			login();
		}
	});
	INPUT_PASSWORD[0].addEventListener('keyup', (event) => {
		if (event.keyCode == 13) { // Enter
			login();
		}
	});
	BTN_LOGIN[0].addEventListener('click', login);
}

$(document).ready(() => {
	api_init(
		null,	// Use default config.
		login_setup
	);
});
