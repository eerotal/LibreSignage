var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var APIInterface = require('libresignage/api/APIInterface');
var Util = require('libresignage/util/Util');
var DisplayView = require('./DisplayView.js');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch(e) {
		if (!('noui' in Util.get_GET_parameters())) {
			new APIErrorDialog(e);
			return;
		} else {
			console.error(e.message);
		}
	}

	let view = new DisplayView(API);
	await view.init();
});
