var $ = require('jquery');
var api = require('ls-api');

var API = null;
var LOGIN_LANDING = "/control";
var INPUT_USERNAME = $("#input-user");
var INPUT_PASSWORD = $("#input-pass");
var BTN_LOGIN = $("#btn-login");
var CHECK_PERM = $("#checkbox-perm-session");

function login_redirect(uri) {
	window.location.assign(uri);
}

function login() {
	API.login(
		INPUT_USERNAME.val(),
		INPUT_PASSWORD.val(),
		CHECK_PERM.is(":checked"),
		(resp) => {
			if (resp.error == API.ERR.API_E_INCORRECT_CREDS) {
				login_redirect("/login?failed=1");
			} else if (resp.error == API.ERR.API_E_OK) {
				if (CHECK_PERM.is(":checked")) {
					login_redirect('/app');
					return;
				}
				login_redirect(LOGIN_LANDING);
			} else {
				API.handle_disp_error(resp.error);
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
	API = new api.API(null, login_setup);
});
