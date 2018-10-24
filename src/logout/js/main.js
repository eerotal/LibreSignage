var $ = require('jquery');
var api = require('ls-api');

var API = null;
const LOGOUT_REDIR_TIME = 2000;

function logout_redirect() {
	setTimeout(() => {
		window.location.href = "/login";
	}, LOGOUT_REDIR_TIME);
}

function logout() {
	if (API.CONFIG.session) {
		API.logout((resp) => {
			if (API.handle_disp_error(resp.error)) {
				return;
			} else {
				logout_redirect();
			}
		});
	} else {
		logout_redirect();
	}
}

$(document).ready(() => {
	API = new api.API(null, logout);
});

