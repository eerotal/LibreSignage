var API_ENDP = {
	CONTENT_LIST: {
		uri:	"/api/content_list.php",
		method:	"GET"
	},
	CONTENT_GET: {
		uri:	"/api/content_get.php",
		method: "GET"
	},
	CONTENT_MK: {
		uri:	"/api/content_mk.php",
		method: "POST"
	},
	CONTENT_RM: {
		uri:	"/api/content_rm.php",
		method: "POST"
	}
}

function _api_construct_param_str(params) {
	var ret = "";
	for (var v in params) {
		if (ret != "") {
			ret += "&";
		}
		ret += encodeURIComponent(v);
		ret += "=";
		ret += encodeURIComponent(params[v]);
	}
	return ret;
}

function api_call(endpoint, params, callback) {
	var params_str = "";
	var req = new XMLHttpRequest();

	req.addEventListener("load", function() {
		var d = null;
		try {
			d = JSON.parse(this.responseText);
		} catch(e) {
			if (e instanceof SyntaxError) {
				console.log("LibreSignage: API: " +
						"Invalid response!");
				d = null;
			}
		}
		callback(d);
	});

	params_str = _api_construct_param_str(params);
	if (endpoint.method == "GET") {
		req.open(endpoint.method, endpoint.uri +
				"?" + params_str);
	} else {
		req.open(endpoint.method, endpoint.uri);
	}

	req.setRequestHeader("Content-Type", "application/" +
				"x-www-form-urlencoded");
	req.send(params_str);
}
