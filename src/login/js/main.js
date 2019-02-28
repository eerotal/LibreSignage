/*
*  JavaScript entry point for the Login page.
*/
var $ = require('jquery');
var LoginView = require('./loginview.js').LoginView;
var APIInterface = require('ls-api').APIInterface;
var APIUI = require('ls-api-ui');

$(document).ready(async () => {
	let API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new LoginView(API);
	view.init();
});
