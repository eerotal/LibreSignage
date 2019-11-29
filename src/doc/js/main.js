var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

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
