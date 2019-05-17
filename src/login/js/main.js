/*
*  JavaScript entry point for the Login page.
*/
var $ = require('jquery');
var LoginView = require('./loginview.js').LoginView;
var APIInterface = require('ls-api').APIInterface;
var APIUI = require('ls-api-ui');

$(document).ready(async () => {
	let API = new APIInterface();
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	let view = new LoginView(API);
	view.init();

	document.querySelector('#input-user').value=getParams().user || '';
	document.querySelector('#input-pass').value=getParams().pass || '';
	if( getParams().user && getParams().pass ) {
		document.querySelector('#btn-login').disabled = false;
		document.querySelector('#btn-login').click();
	}
});

function getParams(url) {
	url = url || window.location.href;
	var params = {};
	var parser = document.createElement('a');
	parser.href = url;
	var query = parser.search.substring(1);
	var vars = query.split('&');
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split('=');
		params[pair[0]] = decodeURIComponent(pair[1]);
	}
	return params;
};
