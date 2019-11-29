var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var UserManagerView = require('./usermanagerview.js').UserManagerView;

var APIInterface = require('libresignage/api/APIInterface');

$(document).ready(async () => {
	var API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		new APIErrorDialog(e);
		return;
	}

	let view = new UserManagerView(API);
	await view.init();
});
