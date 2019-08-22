var $ = require('jquery');
var APIInterface = require('libresignage/APIInterface').APIInterface;
var APIUI = require('ls-api-ui');

var API = null;

$(document).ready(async () => {
	API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
	}
});
