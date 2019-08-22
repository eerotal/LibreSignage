var $ = require('jquery');
var EditorView = require('./editorview.js').EditorView;
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

	let view = new EditorView(API);
	await view.init();
});
