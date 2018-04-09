/*
*  Functionality for setting and reading cookies.
*/

function set_cookie(data) {
	var cookie_str = "";
	if (data !== Object(data)) {
		throw new Error("Invalid cookie data.");
	}
	for (var key in data) {
		cookie_str += key + "=" + data[key] + ";";
	}
	document.cookie = cookie_str;
}

function get_cookies() {
	var cookies = {};
	for (let c of document.cookie.split('; ')) {
		cookies[c.split('=')[0]] = c.split('=')[1];
	}
	return cookies;
}

function get_cookie(name) {
	return get_cookies()[name];
}

function cookie_exists(name) {
	return name in get_cookies();
}

function rm_cookie(data) {
	var ndata = data;
	ndata.expires = 'Thu, 01 Jan 1970 00:00:00 UTC';
	set_cookie(ndata);
}
