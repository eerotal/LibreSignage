var $ = require('jquery');
var APIUI = require('ls-api-ui');

var APIInterface = require('libresignage/api/APIInterface');
var APIEnpoints = require('libresignage/api/APIEndpoints');

var API = null;
const LOGOUT_REDIR_TIME = 2000;

function logout_redirect() {
	setTimeout(() => {
		window.location.href = "/login";
	}, LOGOUT_REDIR_TIME);
}

async function logout() {
	if (API.get_session() != null) {
		try {
			await API.logout();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
	}
	logout_redirect();
}

$(document).ready(async () => {
	API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	await logout();
});

