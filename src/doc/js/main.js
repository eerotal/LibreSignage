var $ = require('jquery');
var APIInterface = require('ls-api').APIInterface;
var APIUI = require('ls-api-ui');

var API = null;

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
	}
});
