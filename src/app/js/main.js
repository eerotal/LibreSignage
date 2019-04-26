var $ = require('jquery');
var APIInterface = require('ls-api').APIInterface;
var APIUI = require('ls-api-ui');
var DisplayView = require('./displayview.js').DisplayView;
var util = require('ls-util');

$(document).ready(async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch(e) {
		if (!('noui' in util.get_GET_parameters())) {
			APIUI.handle_error(e);
			return;
		} else {
			console.error(e.message);
		}
	}

	let view = new DisplayView(API);
	await view.init();
});
