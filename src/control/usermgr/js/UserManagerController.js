var User = require('libresignage/user/User');

/**
* Controller class for UserManagerView.
*/
class UserManagerController {
	/**
	* Construct a new UserManagerController.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
	}

	/**
	* Create a new user.
	*
	* @param {string}  username     The username of the new user.
	* @param {boolean} passwordless Enable passwordless login for the user.
	*
	* @return {User} The newly created User object.
	*/
	async create_user(username, passwordless) {
		let user = new User(this.api);
		await user.create(username, passwordless);
		return user;
	}

	/**
	* Return an array of the users in the LibreSignage instance.
	*/
	async get_users() {
		return (await User.get_all(this.api));
	}

	/**
	* Modify the groups of a user.
	*
	* @param {string} username The name of the User to modify.
	* @param {string[]} groups An array of group names.
	*/
	async save_user(username, groups) {
		let user = new User(this.api);
		await user.load(username);
		user.set_groups(groups);
		await user.save();
	}

	/**
	* Remove a user.
	*
	* @param {string} username The name of the User to remove.
	*/
	async remove_user(username) {
		let user = new User(this.api);
		await user.load(username);
		await user.remove();
	}
}
module.exports = UserManagerController;
