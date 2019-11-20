/**
* @file Entry point for the Editor page.
*/

var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

var APIInterface = require('libresignage/api/APIInterface');
var EditorView = require('./EditorView.js');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		new APIErrorDialog(e);
		return;
	}

	let view = new EditorView(API);
	await view.init();
});
