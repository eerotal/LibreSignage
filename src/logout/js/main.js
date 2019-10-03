/**
* @file Entry point for the Logout page.
*/

var LogoutView = require('./LogoutView');
var APIInterface = require('libresignage/api/APIInterface');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new LogoutView(API);
	await view.init();
});
