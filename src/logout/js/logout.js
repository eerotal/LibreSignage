const LOGOUT_REDIR_TIME = 2000;

function logout_redirect() {
	setTimeout(() => {
		window.location.href = "/login";
	}, LOGOUT_REDIR_TIME);
}

function logout() {
	if (api_authenticated()) {
		api_logout((resp) => {
			if (api_handle_disp_error(resp.error)) {
				return;
			} else {
				logout_redirect();
			}
		});
	} else {
		console.log(
			"Logout: Not logged in, won't " +
			"attempt to logout."
		);
	}
}

$(document).ready(() => {
	api_init(null, logout);
});

