/*
*  LibreSignage API interface implementation.
*  The functions defined in this file should be used to
*  interface with the LibreSignage API.
*/

var API_ENDP = {
	USERS_GET_ALL: {
		uri:	"/api/endpoint/user/users_get_all.php",
		method:	"GET"
	},
	SLIDE_LIST: {
		uri:	"/api/endpoint/slide/slide_list.php",
		method:	"GET"
	},
	SLIDE_DATA_QUERY: {
		uri:	"/api/endpoint/slide/slide_data_query.php",
		method:	"GET"
	},
	SLIDE_GET: {
		uri:	"/api/endpoint/slide/slide_get.php",
		method: "GET"
	},
	SLIDE_SAVE: {
		uri:	"/api/endpoint/slide/slide_save.php",
		method: "POST"
	},
	SLIDE_RM: {
		uri:	"/api/endpoint/slide/slide_rm.php",
		method: "POST"
	},
	LIBRARY_LICENSES: {
		uri:	"/api/endpoint/library_licenses.php",
		method:	"GET"
	},
	LIBRESIGNAGE_LICENSE: {
		uri:	"/api/endpoint/libresignage_license.php",
		methof:	"GET"
	}
}

function _api_construct_param_str(params) {
	/*
	*  Construct the API call parameter string
	*  from a dictionary of parameters.
	*/
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
	/*
	*  Call an API enpoint. The argument 'endpoint' should
	*  be one of the enpoints defined in API_ENDP. 'params'
	*  can be a dictionary of parameters to send with the
	*  API call. Note that 'params' can't have arrays as
	*  the parameter values at least as of yet. The 'callback'
	*  parameter can be a function that is called after the
	*  API call is complete. The parsed API response is passed
	*  to the callback as the first argument. Both 'params' and
	*  'callback' can be left null if they are not needed.
	*/

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
