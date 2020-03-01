var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var APIInterface = require('libresignage/api/APIInterface');
var Util = require('libresignage/util/Util');

document.addEventListener('DOMContentLoaded', async () => {
	let API = new APIInterface();
	let params = Util.get_GET_parameters();

	try {
		await API.init();
	} catch(e) {
		if (!('noui' in params)) {
			new APIErrorDialog(e);
			return;
		} else {
			console.error(e.message);
		}
	}

	// Attempt a passwordless login.
	if ('user' in params) {
		await API.login(params.user, '', true);
	}

	// Redirect the client to the actual display page.
	delete params.user;
	let new_query = Util.querify(params);
	window.location = "/app" + (new_query.length ? `?${new_query}` : "");
});
