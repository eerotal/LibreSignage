var $ = require('jquery');
var APIUI = require('ls-api-ui');

var APIInterface = require('libresignage/api/APIInterface');

var API = null;

$(document).ready(async () => {
	API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
	}
});
