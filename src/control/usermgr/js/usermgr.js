/*
*  User manager implementation for LibreSignage.
*/

var _usermgr_users = [];
var _usermgr_ready = false;

class User {
	set(user, groups, pass) {
		if (!user) {
			throw new Error("Invalid username for " +
					"User object.");
		}
		this.user = user;
		this.groups = groups;
		this.pass = pass;
	}

	save(ready_callback) {
		var data = {
			'user': this.user,
			'groups': this.groups,
			'pass': this.pass
		};
		api_call(API_ENDP.USER_SAVE, data ,function(response) {
			if (!response || response.error) {
				throw new Error("API error while " +
						"saving user data!");
			}
			if (ready_callback) {
				ready_callback();
			}
		});
	}

	load(user, ready_callback) {
		var this_obj = this;
		api_call(API_ENDP.USER_GET, {'user': user},
			function(response) {
				if (!response || response.error) {
					throw new Error(
						"API error while " +
						"loading user data!"
					);
				}
				this_obj.set(response.user.user,
						response.user.groups);
				if (ready_callback) {
					ready_callback();
				}
			}
		);
	}

	remove(ready_callback) {
		api_call(API_ENDP.USER_REMOVE, {'user': this.user},
			function(response) {
				if (!response || response.error) {
					throw new Error(
						"API error while " +
						"trying to remove user!"
					);
				}
				if (ready_callback) {
					ready_callback();
				}
			}
		);
	}

	get_name() {
		return this.user;
	}

	get_groups() {
		return this.groups;
	}

	set_info(info) {
		this.info = info;
	}

	get_info() {
		return this.info;
	}
}

function _usermgr_error_on_not_ready() {
	if (!usermgr_is_ready()) {
		throw new Error("User manager is not ready!");
	}
}

function usermgr_is_ready() {
	return _usermgr_ready;
}

function users_get() {
	/*
	*  Get the current array of users. The caller must
	*  check whether the user manager is ready first
	*  by calling usermgr_is_ready(). If the user manager
	*  is not ready, an error is thrown.
	*/
	_usermgr_error_on_not_ready();
	return _usermgr_users;
}

function user_get_by_name(user) {
	_usermgr_error_on_not_ready();
	var usrs = users_get();
	for (var u in usrs) {
		if (usrs[u].get_name() == user) {
			return usrs[u];
		}
	}
}

function users_add(user) {
	_usermgr_error_on_not_ready();
	_usermgr_users.push(user);
}

function users_load(ready_callback) {
	/*
	*  Load the user data via the API for use in the user manager.
	*  The data loading is done asynchronously. 'ready_callback'
	*  is called when the data loading is finished.
	*/
	api_call(API_ENDP.USERS_GET_ALL, null, function(response) {
		if (!response || response.error) {
			throw new Error('User manager API error!');
		}

		// Convert the response array into User objects.
		var tmp = null;
		_usermgr_ready = false;
		_usermgr_users = [];
		for (var u in response.users) {
			tmp = new User();
			tmp.set(response.users[u].user,
				response.users[u].groups,
				null);
			_usermgr_users.push(tmp);
		}
		_usermgr_ready = true;

		console.log("LibreSignage: Loaded user data!");

		if (ready_callback) {
			ready_callback(_usermgr_users);
		}
	});
}

function user_exists(user) {
	/*
	*  Check if 'user' exists. Returns true if it does
	*  and false otherwise. The caller must check whether
	*  the user manager is ready first by calling
	*  usermgr_is_ready(). If the user manager is not ready,
	*  an error is thrown.
	*/
	_usermgr_error_on_not_ready();
	var usrs = users_get();
	for (var u in usrs) {
		if (usrs[u].get_name() == user) {
			return true;
		}
	}
	return false;
}
