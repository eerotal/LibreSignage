var $ = require('jquery');
var APIInterface = require('libresignage/APIInterface').APIInterface;
var APIUI = require('ls-api-ui');
var UserManagerView = require('./usermanagerview.js').UserManagerView;

$(document).ready(async () => {
	var API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new UserManagerView(API);
	await view.init();
});
