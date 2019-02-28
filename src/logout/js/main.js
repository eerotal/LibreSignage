var $ = require('jquery');
var APIInterface = require('ls-api').APIInterface;
var APIEnpoints = require('ls-api').APIEndpoints;
var APIUI = require('ls-api-ui');

var API = null;
const LOGOUT_REDIR_TIME = 2000;

function logout_redirect() {
	setTimeout(() => {
		window.location.href = "/login";
	}, LOGOUT_REDIR_TIME);
}

async function logout() {
	if (API.config.session != null) {
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
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	await logout();
});

