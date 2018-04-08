/*
*  LibreSignage API interface implementation.
*  The functions defined in this file should be used to
*  interface with the LibreSignage API.
*/

const API_KEY_RENEWAL_HEADROOM = 10;

var API_CONFIG = {
	protocol: null,
	hostname: null,
	configured: false,
	authenticated: false
}

var SERVER_LIMITS = null;
var API_E_MESSAGES = null;
var API_E = null;

var API_ENDP = {
	// -- User management API endpoints --
	USER_GET_QUOTA: {
		uri:	"/api/endpoint/user/user_get_quota.php",
		method: "GET",
		auth:	true
	},
	USER_CREATE: {
		uri:	"/api/endpoint/user/user_create.php",
		method: "POST",
		auth:	true
	},
	USER_REMOVE: {
		uri:	"/api/endpoint/user/user_remove.php",
		method: "POST",
		auth:	true
	},
	USER_SAVE: {
		uri:	"/api/endpoint/user/user_save.php",
		method: "POST",
		auth:	true
	},
	USER_GET: {
		uri:	"/api/endpoint/user/user_get.php",
		method: "GET",
		auth:	true
	},
	USER_GET_CURRENT: {
		uri:	"/api/endpoint/user/user_get_current.php",
		method: "GET",
		auth:	true
	},
	USERS_GET_ALL: {
		uri:	"/api/endpoint/user/users_get_all.php",
		method:	"GET",
		auth:	true
	},

	// -- Slide API endpoints --
	SLIDE_LIST: {
		uri:	"/api/endpoint/slide/slide_list.php",
		method:	"GET",
		auth:	true
	},
	SLIDE_DATA_QUERY: {
		uri:	"/api/endpoint/slide/slide_data_query.php",
		method:	"GET",
		auth:	true
	},
	SLIDE_GET: {
		uri:	"/api/endpoint/slide/slide_get.php",
		method: "GET",
		auth:	true,
	},
	SLIDE_SAVE: {
		uri:	"/api/endpoint/slide/slide_save.php",
		method: "POST",
		auth:	true
	},
	SLIDE_RM: {
		uri:	"/api/endpoint/slide/slide_rm.php",
		method: "POST",
		auth:	true
	},

	// -- Authentication API endpoints --
	AUTH_LOGIN: {
		uri:	"/api/endpoint/auth/auth_login.php",
		method: "POST",
		auth:	false
	},
	AUTH_LOGOUT: {
		uri:	"/api/endpoint/auth/auth_logout.php",
		method: "POST",
		auth:	true
	},
	AUTH_REQ_API_KEY: {
		uri:	"/api/endpoint/auth/auth_req_api_key.php",
		method: "POST",
		auth:	true
	},

	// -- General information API endpoints --
	API_ERR_CODES: {
		uri:	"/api/endpoint/general/api_err_codes.php",
		method:	"GET",
		auth:	false
	},
	API_ERR_MSGS: {
		uri:	"/api/endpoint/general/api_err_msgs.php",
		method:	"GET",
		auth:	false
	},
	SERVER_LIMITS: {
		uri:	"/api/endpoint/general/server_limits.php",
		method: "GET",
		auth:	false
	},
	LIBRARY_LICENSES: {
		uri:	"/api/endpoint/general/library_licenses.php",
		method:	"GET",
		auth:	false
	},
	LIBRESIGNAGE_LICENSE: {
		uri:	"/api/endpoint/general/libresignage_license.php",
		methof:	"GET",
		auth:	false
	}
}

function _api_chk_configured() {
	if (!API_CONFIG.configured) {
		throw new Error("API: Not initialized");
	}
}

function _api_chk_authenticated() {
	if (!API_CONFIG.authenticated) {
		throw new Error("API: Not authenticated.");
	}
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

	_api_chk_configured();
	if (endpoint.auth) { _api_chk_authenticated(); }

	var data_str = "";
	var ajax_settings = {
		url: endpoint.uri,
		method: endpoint.method,
		complete: function(jqxhr, status) {
			var d = null;
			if (status != 'success') {
				console.error("API: XHR failed.");
				callback({'error': API_E.INTERNAL});
				return;
			}

			try {
				d = JSON.parse(jqxhr.responseText);
			} catch(e) {
				if (e instanceof SyntaxError) {
					console.error("API: Invalid " +
						"response syntax.");
					d = {'error': API_E.INTERNAL};
				}
			}
			callback(d);
		}
	};

	switch (endpoint.method) {
		case "POST":
			ajax_settings.data = JSON.stringify(data);
			ajax_settings.content =
				'application/json';
			break;
		case "GET":
			// Let jQuery encode the data.
			ajax_settings.data = data;
			ajax_settings.content =
				'application/x-www-form-urlencoded';
			break;
		default:
			throw new Error(
				`Invalid endpoint method
				'${endpoint.method}'.`
			);
	}

	if (endpoint.auth) {
		ajax_settings.headers = {
			'Api-Key': get_cookie('api_key')
		};
	}
	$.ajax(ajax_settings);
}

function api_handle_disp_error(err, callback) {
	_api_chk_configured();

	var h = "";
	var p = "";

	if (err == 0) { return 0; }

	if (!API_E_MESSAGES) {
		h = "Error";
		p = "An error was encountered, but a more detailed " +
			"error description can't be shown because the " +
			"error messages haven't been loaded.";
	} else if (err in Object.keys(API_E_MESSAGES)) {
		h = API_E_MESSAGES[err].short;
		p = API_E_MESSAGES[err].long;
	} else {
		h = "Unknown error";
		p = "The server encountered an unknown error.";
	}
	dialog(DIALOG.ALERT, h, p, callback);
	console.error("API: " + p);
	return err;
}

function api_load_error_codes(callback) {
	api_call(API_ENDP.API_ERR_CODES, null, (resp) => {
		if (api_handle_disp_error(resp.error)) {
			throw new Error("API: Failed to load " +
					"error codes.");
			return;
		}
		API_E = resp.codes;
		if (callback) {
			callback();
		}
	});
}

function api_load_error_msgs(callback) {
	api_call(API_ENDP.API_ERR_MSGS, null, (resp) => {
		if (api_handle_disp_error(resp.error)) {
			throw new Error("API: Failed to load " +
					"error messages.");
		}
		API_E_MESSAGES = resp.messages;
		if (callback) {
			callback();
		}
	});
}

function api_load_limits(callback) {
	api_call(API_ENDP.SERVER_LIMITS, null, (resp) => {
		if (api_handle_disp_error(resp.error)) {
			throw new Error("API: Failed to load limits.");
		}
		SERVER_LIMITS = resp.limits;
		if (callback) {
			callback();
		}
	});
}

function api_host() {
	/*
	*  Get the API host URL.
	*/
	_api_chk_configured();
	return API_CONFIG.protocol + "\/\/" + API_CONFIG.hostname;
}

function api_apply_config(config) {
	/*
	*  Apply the API config from 'config'.
	*/
	var tmp = config;
	console.log("API: Configuring API interface.")
	if (tmp == null) {
		tmp = {};
	} else if (tmp !== Object(tmp)) {
		throw new Error(
			"Invalid type for 'config'. " +
			"Expected object or null."
		);
	}

	if (tmp.protocol) {
		console.log("API: Protocol: " + tmp.protocol);
		API_CONFIG.protocol = tmp.protocol;
	} else {
		console.log("API: Using default protocol.")
		API_CONFIG.protocol = window.location.protocol;
	}

	if (tmp.hostname) {
		console.log("API: Hostname: " + tmp.hostname);
		API_CONFIG.hostname = tmp.hostname;
	} else {
		console.log("API: Using default hostname.");
		API_CONFIG.hostname = window.location.hostname;
	}
}

function api_key_schedule_renewal() {
	/*
	*  Schedule API key renewal just before the
	*  API key expires.
	*/

	if (!cookie_exists('api_key')) {
		throw new Error(
			"API: Can't schedule cookie renewal when " +
			"no API key exists."
		);
	}

	var created = parseInt(get_cookie('api_key_created'), 10);
	var max_age = parseInt(get_cookie('api_key_max_age'), 10);

	var left = created + max_age - Date.now()/1000;
	var t = left - API_KEY_RENEWAL_HEADROOM;

	if (left <= 0) {
		API_CONFIG.authenticated = false;
		throw new Error(
			"API: Won't schedule key renewal because " +
			"the API key is already expired."
		);
	} else if (t <= 0) {
		// Attempt to renew the key now since it will expire soon.
		api_key_renew();
	}

	console.log("API: Key renewal in " + t + " seconds.");
	setTimeout(api_key_renew, t*1000);
}

function api_key_renew() {
	/*
	*  Renew the stored API key.
	*/
	console.log("API: Renewing API key.");
	api_call(
		API_ENDP.AUTH_REQ_API_KEY,
		null,
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				API_CONFIG.authenticated = false;
				throw new Error("API: Failed to " +
						"renew API key.");
			}
			api_key_store(
				resp.api_key.api_key,
				resp.api_key.created,
				resp.api_key.max_age
			);
			API_CONFIG.authenticated = true;

			console.log("API: Key renewal complete.");
			api_key_schedule_renewal();
		}
	)
}

function api_key_store(api_key, created, max_age) {
	/*
	*  Store the supplied API key data in cookies.
	*/
	console.log("API: Store API key data.")
	set_cookie({"api_key": api_key, "path": "/"});
	set_cookie({"api_key_created": created, "path": "/"});
	set_cookie({"api_key_max_age": max_age, "path": "/"});
}

function api_login(user, pass) {
	/*
	*  Login using the supplied credentials and store the
	*  returned API key.
	*/
	api_call(
		API_ENDP.AUTH_LOGIN,
		{username: user, password: pass},
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				console.error(
					"API: Authentication failed."
				);
				return;
			}
			api_key_store(
				resp.api_key.api_key,
				resp.api_key.created,
				resp.api_key.max_age
			);
			API_CONFIG.authenticated = true;

			api_key_schedule_renewal();
		}
	);
}

function api_init(config, callback) {
	/*
	*  Initialize the API interface.
	*/
	if (API_CONFIG.configured) { return; }
	api_apply_config(config);
	API_CONFIG.configured = true;

	api_load_error_codes(() => {
		api_load_error_msgs(() => {
			api_load_limits(() => {
				console.log("API: Initialized.");
				if (callback) {
					callback();
				}
			});
		});
	});
}
