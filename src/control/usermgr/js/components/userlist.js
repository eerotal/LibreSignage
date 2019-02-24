var $ = require('jquery');
var UserListEntry = require('./userlistentry.js').UserListEntry;

class UserList {
	constructor(api) {
		this.api = api;
		this.users = [];
		this.list = [];
	}

	set_user_data(users) {
		this.users = users;
	}

	render(where) {
		this.list = [];

		$(this.where).html('');
		for (let u of Object.values(this.users)) {
			this.list.push(new UserListEntry(this.api, u));
			this.list[this.list.length - 1].render(where);
		}
	}
}
exports.UserList = UserList;
