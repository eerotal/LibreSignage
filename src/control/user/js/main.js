var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var UserView = require('./userview.js').UserView;

var APIInterface = require('libresignage/api/APIInterface');

$(document).ready(async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		new APIErrorDialog(e);
		return;
	}

	let view = new UserView(API);
	await view.init();
});
