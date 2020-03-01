var MultiSelect = require('libresignage/ui/components/MultiSelect');
var StrValidator = require('libresignage/ui/validator/StrValidator');
var DropConfirm = require('libresignage/ui/components/DropConfirm');
var BaseComponent = require('libresignage/ui/components/BaseComponent');

/**
* UserListEntry component for UserList.
*/
class UserListEntry extends BaseComponent {
	/**
	* Construct a new UserListEntry object.
	*
	* @param {APIInterface} api       An APIInterface object.
	* @param {User}         user      The User object to use for the entry.
	* @param {HTMLElement}  container The container element of the entry.
	*/
	constructor(api, user, container) {
		super();

		this.api = api;
		this.user = user;
		this.container = container;
		this.entry_node = null;

		this.init_state({
			'remove_pending': false,
			'save_pending': false
		});
	}

	/**
	* Make the DOM node for a UserListEntry.
	*
	* @param {string} name     The name of the user.
	* @param {string} password The password of the user (if known).
	*/
	static make_entry_node(name, password) {
		let div = document.createElement('DIV');
		div.id = `user-list-entry-${name}`;
		div.class = 'card';

		div.innerHTML = `
			<div
				class="card-header"
				id="heading-${name}">
				<a class="link-nostyle"
					data-toggle="collapse"
					href="#collapse-${name}"
					aria-expanded="true"
					aria-controls="collapse-${name}">
					<i class="fas fa-caret-down"></i>&nbsp;${name}
				</a>
			</div>
			<div
				class="collapse"
				id="collapse-${name}"
				aria-labelledby="heading-${name}"
				data-parent="#users-table">
				<div class="card-body">
					<table class="input-container">
						<tr>
							<td>
								<label
									for="${name}-name">
									Username
								</label>
							</td>
							<td>
								<input
									type="input"
									id="${name}-name"
									class="form-control"
									value="${name}"
									disabled>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<label
									for="${name}-password">
									Password
								</label>
							</td>
							<td>
								<input
									type="input"
									id="${name}-password"
									class="form-control"
									value="${password}"
									disabled>
								</input>
							</td>
						</tr>
						<tr>
							<td>
								<label
									for="${name}-groups">
									Groups
								</label>
							</td>
							<td>
								<div id="${name}-groups-group"></div>
							</td>
						</tr>
						<tr>
						</tr>
					</table>
					<div class="btn-container">
						<button
							id="btn-user-save-${name}"
							class="btn-user-save btn btn-success">
							<i class="fas fa-save"></i>
						</button>
						<div id="btn-user-remove-${name}"
							class="btn-user-remove">
						</div>
					</div>
				</div>
			</div>
		`;

		return div;
	}

	/**
	* Render a UserListEntry.
	*/
	render() {
		let name = this.user.get_user();
		let pass_show = "";

		if (this.user.get_password() != null) {
			pass_show = this.user.get_password();
		} else if (this.user.is_passwordless()) {
			pass_show = "(No password)";
		} else {
			pass_show = "**********";
		}

		// Create the DOM element for the entry.
		this.entry_node = UserListEntry.make_entry_node(name, pass_show);
		this.container.appendChild(this.entry_node);

		// Initialize the groups MultiSelect.
		this.groups = new MultiSelect(
			`${name}-groups-group`,
			`${name}-groups`,
			[
				new StrValidator(
					{
						min: 1,
						max: null,
						regex: null
					}, '', true
				),
				new StrValidator(
					{
						min: null,
						max: this.api.limits.MAX_USER_GROUP_LEN,
						regex: null
					}, 'The group name is too long.'
				),
				new StrValidator(
					{
						min: null,
						max: null,
						regex: /^[A-Za-z0-9_]*$/
					}, 'The group name contains invalid characters.'
				)
			],
			{
				nodups: true,
				maxopts: this.api.limits.MAX_USER_GROUPS
			}
		)
		this.groups.set(this.user.get_groups());

		// Initialize the remove button DropConfirm.
		this.remove = new DropConfirm(
			document.querySelector(`#btn-user-remove-${name}`)
		);
		this.remove.set_button_html('<i class="fas fa-trash-alt"></i>');
		this.remove.set_content_html('Remove user?');

		// Initialize the remove DropConfirm event handling.
		document.querySelector(`#btn-user-remove-${name}`).addEventListener(
			'component.dropconfirm.confirm',
			() => {
				this.state('remove_pending', true);
				this.entry_node.dispatchEvent(
					new Event('component.userlistentry.remove')
				);
			}
		);

		// Initialize the save button event handling.
		document.querySelector(`#btn-user-save-${name}`).addEventListener(
			'click',
			() => {
				this.state('save_pending', true);
				this.entry_node.dispatchEvent(
					new Event('component.userlistentry.save')
				);
			}
		);
	}

	/**
	* Get the User object of a UserListEntry.
	*
	* @return {User} A User object.
	*/
	get_user() {
		return this.user;
	}

	/**
	* Get new User groups as an array.
	*
	* @param {string[]} New User groups for a User.
	*/
	get_new_groups() {
		return this.groups.get_selected();
	}

	/**
	* Get the root DOM element of a UserListEntry.
	*
	* @return {HTMLElement} The root HTMLElement.
	*/
	get_element() {
		return document.querySelector(
			`#user-list-entry-${this.user.get_user()}`
		);
	}
}
module.exports = UserListEntry;
