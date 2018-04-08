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

function get_cookie(key) {
	return get_cookies()[key];
}

function cookie_exists(key) {
	return key in get_cookies();
}
