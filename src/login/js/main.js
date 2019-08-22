/*
*  JavaScript entry point for the Login page.
*/
var $ = require('jquery');
var LoginView = require('./loginview.js').LoginView;
var APIUI = require('ls-api-ui');

var APIInterface = require('libresignage/api/APIInterface');

$(document).ready(async () => {
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
