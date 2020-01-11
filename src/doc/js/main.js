var APIInterface = require('libresignage/api/APIInterface');
var Util = require('libresignage/util/Util');

document.addEventListener('DOMContentLoaded', () => {
	Util.await_and_watch_for_errors(async () => {
		let API = new APIInterface();
		try {
			await API.init();
		} catch (e) {
			APIUI.handle_error(e);
		}
	});
});
