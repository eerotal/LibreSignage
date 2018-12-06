var $ = require('jquery');
var EditorView = require('./editorview.js').EditorView;
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

	let view = new EditorView(API);
	await view.init();
})
