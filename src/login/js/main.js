/**
* @file Entry point for the Login page.
*/

var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

var APIInterface = require('libresignage/api/APIInterface');
var LoginView = require('./LoginView.js');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		new APIErrorDialog(e);
		return;
	}

	let view = new LoginView(API);
	view.init();
});
