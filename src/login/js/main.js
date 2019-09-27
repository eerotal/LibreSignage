/**
* @file Entry point for the Login page.
*/

var APIUI = require('ls-api-ui');

var APIInterface = require('libresignage/api/APIInterface');
var LoginView = require('./LoginView.js');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new LoginView(API);
	view.init();
});
