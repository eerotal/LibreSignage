var $ = require('jquery');
var APIUI = require('ls-api-ui');
var UserView = require('./userview.js').UserView;

var APIInterface = require('libresignage/api/APIInterface');

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
