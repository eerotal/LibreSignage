var User = require('ls-user').User;

class UserManagerController {
	constructor(api) {
		this.api = api;
	}

	async create_user(username) {
		/*
		*  Create a new user with the username 'username' and
		*  return the resulting User object.
		*/
		let user = new User(this.api);
		await user.create(username);
		return user;
	}

	async get_users() {
		/*
		*  Return an array of the users in the LibreSignage instance.
		*/
		return (await User.get_all(this.api));
	}

	async save_user(username, groups) {
		/*
		*  Save the user 'username' with the new groups 'groups'.
		*/
		let user = new User(this.api);
		await user.load(username);
		user.set_groups(groups);
		await user.save();
	}

	async remove_user(username) {
		/*
		*  Remove the user 'username'.
		*/
		let user = new User(this.api);
		await user.load(username);
		await user.remove();
	}
}
exports.UserManagerController = UserManagerController;
