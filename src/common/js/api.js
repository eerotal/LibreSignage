/*
*  LibreSignage API interface implementation.
*  The functions defined in this file should be used to
*  interface with the LibreSignage API.
*/

const SESSION_RENEWAL_HEADROOM = 30;

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
	AUTH_LOGOUT_OTHER: {
		uri:	"/api/endpoint/auth/auth_logout_other.php",
		method: "POST",
		auth:	true
	},
	AUTH_SESSION_RENEW: {
		uri:	"/api/endpoint/auth/auth_session_renew.php",
		method: "POST",
		auth:	true
	},
	AUTH_GET_SESSIONS: {
		uri:	"/api/endpoint/auth/auth_get_sessions.php",
		method: "GET",
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

function _api_ensure_configured() {
	if (!api_configured) {
		throw new Error("API: Not initialized");
	}
}

function _api_ensure_authenticated() {
	if (!api_authenticated) {
		throw new Error("API: Not authenticated.");
	}
}

function api_configured() {
	return API_CONFIG.configured;
}

function api_authenticated() {
	return API_CONFIG.authenticated;
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

	_api_ensure_configured();
	if (endpoint.auth) { _api_ensure_authenticated(); }

	var data_str = "";
	var ajax_settings = {
		url: `${api_host()}/${endpoint.uri}`,
		method: endpoint.method,
		dataType: 'json',
		error: function(jqhxr, status, exception) {
			console.error(
				`API: XHR failed. ` +
				`(status: ${status})`
			);
			callback({'error': API_E.API_E_INTERNAL});
		},
		success: function(data, status, jqxhr) {
			callback(data);
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
			'Auth-Token': get_cookie('session_token')
		};
	}
	$.ajax(ajax_settings);
}

function api_handle_disp_error(err, callback) {
	_api_ensure_configured();

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
	_api_ensure_configured();
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

function session_schedule_renewal() {
	/*
	*  Schedule a session renewal just before the
	*  existing session expires.
	*/
	_api_ensure_authenticated();

	if (get_cookie('session_permanent') == '1') {
		console.log(
			"API: Won't schedule session renewal " +
			"for a permanent session."
		);
		return;
	}

	var created = parseInt(get_cookie('session_created'), 10);
	var max_age = parseInt(get_cookie('session_max_age'), 10);

	if (max_age <= SESSION_RENEWAL_HEADROOM) {
		throw new Error(
			"Session max_age too low. " +
			"(max_age <= SESSION_RENEWAL_HEADROOM)"
		);
	} else if (max_age <= SESSION_RENEWAL_HEADROOM + 10) {
		throw new Error(
			"Session max_age is so low that the " +
			"session would be renewed very often " +
			"causing a high load on the client and " +
			"server. Session renewal won't be scheduled."
		);
	}

	var left = created + max_age - Date.now()/1000;
	var t = left - SESSION_RENEWAL_HEADROOM;

	if (left <= 0) {
		session_remove();
		console.error(
			"API: Won't schedule session renewal because " +
			"the session is already expired."
		);
		return;
	} else if (t <= 0) {
		// Attempt to renew the session now.
		session_renew();
	}
	console.log("API: Session renewal in " + t + " seconds.");
	setTimeout(session_renew, t*1000);
}

function session_renew() {
	/*
	*  Renew the stored session.
	*/
	console.log("API: Renew session.");
	api_call(
		API_ENDP.AUTH_SESSION_RENEW,
		null,
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				console.error("API: Session renewal " +
						"failed.");
				session_remove();
				return;
			}
			console.log("API: Session renewal complete.");
			session_schedule_renewal();
		}
	)
}

function session_remove() {
	/*
	*  Remove the session data cookies.
	*/
	API_CONFIG.authenticated = false;
	rm_cookie({"session_token": "", "path": "/"});
	rm_cookie({"session_created": "", "path": "/"});
	rm_cookie({"session_max_age": "", "path": "/"});
	rm_cookie({"session_permanent": "", "path": "/"});
}

function session_check() {
	/*
	*  Check the authentication cookies and flag the API
	*  as authenticated if the cookies are valid. A session
	*  renewal is also scheduled if the cookies are valid.
	*/
	console.log("API: Check authentication status.");
	if (cookie_exists('session_token') &&
		cookie_exists('session_created') &&
		cookie_exists('session_max_age')) {
		/*
		*  Because the cookies exist, the session is not
		*  expired yet. (The cookie parameter 'expire' is
		*  set by the server.)
		*/
		console.log("API: Already authenticated.")
		API_CONFIG.authenticated = true;
		session_schedule_renewal();
	} else {
		// Remove invalid cookies.
		console.log("API: Invalid or no session data.");
		session_remove();
	}
}

function api_login(user, pass, perm, ready_callback) {
	/*
	*  Login using the supplied credentials and store the
	*  returned session data. ready_callback is called when
	*  the login is successfully finished.
	*/
	console.log("API: Authenticate");
	api_call(
		API_ENDP.AUTH_LOGIN,
		{
			username: user,
			password: pass,
			permanent: perm,
			who: "LibreSignage-Web-Interface"
		},
		(resp) => {
			if (resp.error == API_E.API_E_OK) {
				API_CONFIG.authenticated = true;
				session_schedule_renewal();
			} else {
				console.error("API: Auth failed.");
				session_remove();
			}

			if (ready_callback) {
				ready_callback(resp);
			}
		}
	);
}

function api_logout(ready_callback) {
	/*
	*  Call the logout API endpoint and remove session
	*  data cookies. ready_callback is called when the
	*  logout is successfully finished.
	*/
	api_call(
		API_ENDP.AUTH_LOGOUT,
		null,
		(resp) => {
			if (resp.error == API_E.API_E_OK) {
				// Remove session data cookies.
				session_remove();
			} else {
				console.error("API: Logout failed.");
			}
			if (ready_callback) {
				ready_callback(resp);
			}
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

	session_check();

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
