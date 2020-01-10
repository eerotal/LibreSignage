var UserListEntry = require('./UserListEntry.js');

/**
* UserList component for the User Manager page.
*/
class UserList {
	/**
	* Construct a new UserList.
	*
	* @param {APIInterface} api    An APIInterface object.
	* @param {HTMLElement} element The container element for the UserList.
	*/
	constructor(api, element) {
		this.api = api;
		this.element = element;
		this.users = [];
		this.list = [];
	}

	/**
	* Set the users of the list.
	*
	* @param {User[]} A list of User objects to show in the list.
	*/
	set_user_data(users) {
		this.users = users;
	}

	/**
	* Add a User.
	*
	* @param {User} The User object to add.
	*/
	add_user(user) {
		this.users[user.get_user()] = user;
	}

	/**
	* Get the list of UserListEntry objects currently displayed.
	*
	* @return {UserListEntry[]} A list of entries.
	*/
	get_entries() {
		return this.list;
	}

	/**
	* Update the UserList UI elements.
	*/
	update() {
		this.list = [];

		this.element.innerHTML = '';
		for (let user of Object.values(this.users)) {
			let entry = new UserListEntry(this.api, user, this.element);
			this.list.push(entry);
			entry.render();

			// Create event listeners for the user save/remove events.
			entry.get_element().addEventListener(
				'component.userlistentry.save',
				() => {
					this.element.dispatchEvent(
						new Event('component.userlist.save')
					);
				}
			)
			entry.get_element().addEventListener(
				'component.userlistentry.remove',
				() => {
					this.element.dispatchEvent(
						new Event('component.userlist.remove')
					);
				}
			)
		}
	}
}
module.exports = UserList;
