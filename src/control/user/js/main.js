/**
* @file Entry point for the User Settings page.
*/

var APIInterface = require('libresignage/api/APIInterface');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var Util = require('libresignage/util/Util');
var UserView = require('./UserView.js');

document.addEventListener('DOMContentLoaded', () => {
	Util.await_and_watch_for_errors(async () => {
		let API = new APIInterface();
		try {
			await API.init();
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}

		let view = new UserView(API);
		await view.init();
	}, window);
});
