function get_GET_parameters() {
	/*
	*  Get the HTTP GET parameters in an associative array.
	*/
	var query_str = window.location.search.substr(1);
	var params_strs = [];
	var params = [];
	var tmp = [];

	if (!query_str.length) {
		return [];
	} else {
		params_strs = query_str.split('&');
	}

	for (var i in params_strs) {
		tmp = params_strs[i].split('=');
		params[decodeURIComponent(tmp[0])] =
			decodeURIComponent(tmp[1]);
	}
	return params;
}

function get_cookies() {
	var ret = {};
	var tmp = document.cookie.split('; ');
	for (let c of tmp) {
		ret[c.split('=')[0]] = c.split('=')[1];
	}
	return ret;
}

function sanitize_html(src) {
	// Sanitize HTML tags.
	return $("<div></div>").text(src).html();
}

function setup_defaults() {
	// Setup tooltips.
	$('[data-toggle="tooltip"]').tooltip({
		'delay': {
			'show': 800,
			'hide': 50
		},
		'trigger': 'hover'
	});
}
