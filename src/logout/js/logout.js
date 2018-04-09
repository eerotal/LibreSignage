const LOGOUT_REDIR_TIME = 2000;

function logout_redirect() {
	setTimeout(() => {
		window.location.href = "/login";
	}, LOGOUT_REDIR_TIME);
}

function logout() {
	api_logout((err) => {
		if (api_handle_disp_error(err)) {
			return;
		} else {
			logout_redirect();
		}
	});
}

$(document).ready(() => {
	api_init(null, logout);
});

