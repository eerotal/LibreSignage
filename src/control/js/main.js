/*
*  Entry point for the LibreSignage control panel JavaScript.
*/
var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var ControlPanelView = require('./controlpanelview.js').ControlPanelView;

var APIInterface = require('libresignage/api/APIInterface');

$(document).ready(async () => {
	API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		new APIErrorDialog(e);
		return;
	}

	let view = new ControlPanelView(API);
});
