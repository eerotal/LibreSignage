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
		return (await user.create(username));
	}

	async get_users() {
		/*
		*  Return an array of the users in the LibreSignage instance.
		*/
		return (await User.get_all(this.api));
	}
}
exports.UserManagerController = UserManagerController;
