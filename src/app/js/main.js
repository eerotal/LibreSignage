var $ = require('jquery');
var APIUI = require('ls-api-ui');
var DisplayView = require('./displayview.js').DisplayView;

var APIInterface = require('libresignage/api/APIInterface');
var Util = require('libresignage/util/Util');

$(document).ready(async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch(e) {
		if (!('noui' in Util.get_GET_parameters())) {
			APIUI.handle_error(e);
			return;
		} else {
			console.error(e.message);
		}
	}

	let view = new DisplayView(API);
	await view.init();
});
