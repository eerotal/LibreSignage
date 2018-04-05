var LOGIN_LANDING = "/control";
var INPUT_USERNAME = $("#input-user");
var INPUT_PASSWORD = $("#input-pass");
var BTN_LOGIN = $("#btn-login");

function login_redirect(uri) {
	window.location.assign(uri);
}

function login() {
	api_call(
		API_ENDP.AUTH_LOGIN,
		{
			username: INPUT_USERNAME.val(),
			password: INPUT_PASSWORD.val()
		},
		(resp) => {
			if (resp.error == API_E.API_E_INCORRECT_CREDS) {
				login_redirect("/login?failed=1");
			} else if (resp.error == API_E.API_E_OK) {
				login_redirect(LOGIN_LANDING);
			} else {
				api_handle_disp_error(resp.error);
			}
		}
	);
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

api_init(login_setup);
