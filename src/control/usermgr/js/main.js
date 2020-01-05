/**
* @file Entry point for the User Manager page.
*/

var Util = require('libresignage/util/Util');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var APIInterface = require('libresignage/api/APIInterface');
var UserManagerView = require('./UserManagerView.js');

document.addEventListener('DOMContentLoaded', () => {
	Util.await_and_watch_for_errors(async () => {
		let API = new APIInterface();
		try {
			await API.init();
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}

		let view = new UserManagerView(API);
		await view.init();
	}, window);
});
