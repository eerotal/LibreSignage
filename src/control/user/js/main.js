var $ = require('jquery');
var APIInterface = require('libresignage/APIInterface').APIInterface;
var APIUI = require('ls-api-ui');
var UserView = require('./userview.js').UserView;

$(document).ready(async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new UserView(API);
	await view.init();
});
