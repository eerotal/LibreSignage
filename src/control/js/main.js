/*
*  Entry point for the LibreSignage control panel JavaScript.
*/
var $ = require('jquery');
var APIUI = require('ls-api-ui');
var ControlPanelView = require('./controlpanelview.js').ControlPanelView;

var APIInterface = require('libresignage/api/APIInterface');

$(document).ready(async () => {
	API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new ControlPanelView(API);
});
