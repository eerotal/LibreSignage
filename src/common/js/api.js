/*
*  LibreSignage API interface implementation.
*  The functions defined in this file should be used to
*  interface with the LibreSignage API.
*/

var API_ENDP = {
	// -- User management API endpoints --
	USER_GET_QUOTA: {
		uri:	"/api/endpoint/user/user_get_quota.php",
		method: "GET"
	},
	USER_CREATE: {
		uri:	"/api/endpoint/user/user_create.php",
		method: "POST"
	},
	USER_REMOVE: {
		uri:	"/api/endpoint/user/user_remove.php",
		method: "POST"
	},
	USER_SAVE: {
		uri:	"/api/endpoint/user/user_save.php",
		method: "POST"
	},
	USER_GET: {
		uri:	"/api/endpoint/user/user_get.php",
		method: "GET"
	},
	USER_GET_CURRENT: {
		uri:	"/api/endpoint/user/user_get_current.php",
		method: "GET"
	},
	USERS_GET_ALL: {
		uri:	"/api/endpoint/user/users_get_all.php",
		method:	"GET"
	},

	// -- Slide API endpoints --
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

	// -- General information API endpoints --
	LIBRARY_LICENSES: {
		uri:	"/api/endpoint/library_licenses.php",
		method:	"GET"
	},
	LIBRESIGNAGE_LICENSE: {
		uri:	"/api/endpoint/libresignage_license.php",
		methof:	"GET"
	}
}

function _api_construct_GET_data(data) {
	/*
	*  Construct the API call data string
	*  for a GET request from an associative
	*  array or object.
	*/
	var ret = "";
	for (var v in data) {
		if (typeof data[v] != 'string' &&
			typeof data[v] != 'number') {
			throw new Error("GET parameters can only be " +
					"numbers or strings!");
		}
		if (ret != "") {
			ret += "&";
		}
		ret += encodeURIComponent(v);
		ret += "=";
		ret += encodeURIComponent(data[v]);
	}
	return ret;
}

function api_call(endpoint, data, callback) {
	/*
	*  Call an API enpoint. The argument 'endpoint' should
	*  be one of the enpoints defined in API_ENDP. 'data'
	*  can be an object containing the data to send with the
	*  API request. The 'callback' argument can be a function
	*  that is called after the API call is complete. The
	*  parsed API response is passed to the callback as the
	*  first argument. Both 'data' and 'callback' can be
	*  left null if they are not needed.
	*/

	var data_str = "";
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

	if (endpoint.method == "GET") {
		/*
		*  Send the GET data in the URL with the
		*  content type x-www-form-urlencoded.
		*/
		data_str = _api_construct_GET_data(data);
		req.open(endpoint.method, endpoint.uri +
				"?" + data_str);
		req.setRequestHeader("Content-Type",
			"application/x-www-form-urlencoded");
		req.send();
	} else {
		/*
		*  Send the POST data as JSON in the request body.
		*/
		data_str = JSON.stringify(data);
		req.open(endpoint.method, endpoint.uri);
		req.setRequestHeader("Content-Type", "application/json");
		req.send(data_str);
	}
}
