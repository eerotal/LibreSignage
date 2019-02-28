var $ = require('jquery');
var UserListEntry = require('./userlistentry.js').UserListEntry;

class UserList {
	constructor(api, element) {
		this.api = api;
		this.element = element;
		this.users = [];
		this.list = [];
	}

	set_user_data(users) {
		/*
		*  Set the array of userdata.
		*/
		this.users = users;
	}

	add_user(user) {
		/*
		*  Add a user to the userdata list.
		*/
		this.users[user.get_user()] = user;
	}

	render() {
		this.list = [];

		$(this.element).html('');
		for (let user of Object.values(this.users)) {
			let entry = new UserListEntry(this.api, user, this.element);
			this.list.push(entry);
			entry.render();

			// Create event listeners for the user save/remove events.
			$(entry.get_element()).on(
				'component.userlistentry.save',
				(event, data) => this.trigger('save', data)
			)
			$(entry.get_element()).on(
				'component.userlistentry.remove',
				(event, data) => this.trigger('remove', data)
			)
		}
	}

	trigger(name, data) {
		$(this.element).trigger(
			`component.userlist.${name}`,
			data
		);
	}
}
exports.UserList = UserList;
