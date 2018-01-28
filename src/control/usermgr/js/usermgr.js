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
}

function _usermgr_error_on_not_ready() {
	if (!usermgr_is_ready()) {
		throw new Error("User manager is not ready!");
	}
}

function _usermgr_error_on_invalid_user(user) {
	if (!user_exists(user)) {
		throw new Error("User '" + user + "' doesn't exist.");
	}
}

function usermgr_is_ready() {
	return _usermgr_ready;
}

function users_get() {
	_usermgr_error_on_not_ready();
	return _usermgr_users;
}

function users_load(ready_callback) {
	/*
	*  Load the user data for use in the usermgr.
	*/
	_usermgr_ready = false;
	api_call(API_ENDP.USERS_GET_ALL, null, function(response) {
		if (!response || response.error) {
			throw new Error('User manager API error!');
		}

		_usermgr_users = response.users;
		_usermgr_ready = true;
		console.log("LibreSignage: Loaded user data!");

		if (ready_callback) {
			ready_callback(_usermgr_users);
		}
	});
}

function users_save(user) {
	/*
	*  Save the existing user data via the API.
	*/
	_usemgr_error_on_not_ready();

	api_call(API_ENDP.USER_SAVE, user, function(response) {

	});
}

function user_exists(user) {
	/*
	*  Check if 'user' exists. Returns true if it does
	*  and false otherwise.
	*/
	_usermgr_error_on_not_ready();
	return Object.keys(users_get()).indexOf(user) != -1;
}

function user_is_in_group(user, group) {
	/*
	*  Check whether 'user' is in 'group'.
	*  Returns true if it is and false otherwise.
	*/
	_usermgr_error_on_not_ready();
	_usermgr_error_on_invalid_user(user);
	return users_get()[user].groups.indexOf(group) != -1;
}

function user_get_groups(user) {
	_usermgr_error_on_not_ready();
	_usermgr_error_on_invalid_user(user);
	return users_get()[user].groups;
}

function user_add_group(user, group) {
	/*
	*  Add the user 'user' to 'group'.
	*/
	_usermgr_error_on_not_ready();
	_usermgr_error_on_invalid_user(user);
	if (user_is_in_group(user, group)) {
		return;
	}
	users_get()[user].groups.push(group);
}

function user_remove_group(user, group) {
	/*
	*  Remove the user 'user' from 'group'.
	*/
	var i = 0;
	_usermgr_error_on_not_ready();
	_usermgr_error_on_invalid_user(user);
	i = users_get()[user].groups.indexOf(group);
	if (i != -1) {
		users_get()[user].groups.splice(i, 1);
	}
}
