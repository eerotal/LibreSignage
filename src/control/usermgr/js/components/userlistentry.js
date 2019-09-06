var $ = require('jquery');
var DropConfirm = require('ls-dropconfirm').DropConfirm;
var EventData = require('ls-eventdata').EventData;

var MultiSelect = require('libresignage/ui/components/MultiSelect');
var StrValidator = require('libresignage/ui/validator/StrValidator');

// Template for the UserListEntry HTML.
const user_list_entry = (name, password) => `
	<div id="user-list-entry-${name}" class="card">
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
	</div>
`;

class UserListEntry {
	constructor(api, user, container) {
		this.api = api;
		this.user = user;

		this.container = container;
	}

	render() {
		let pass = this.user.get_password();
		let name = this.user.get_user();

		// Create the UserListEntry HTML.
		$(this.container).append(user_list_entry(
			name,
			pass != null ? pass : '(Hidden)'
		));

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
		this.remove = new DropConfirm($(`#btn-user-remove-${name}`)[0]);
		this.remove.set_button_html('<i class="fas fa-trash-alt"></i>');
		this.remove.set_content_html('Remove user?');

		// Initialize the remove DropConfirm event handling.
		$(`#btn-user-remove-${name}`).on(
			'component.dropconfirm.confirm',
			() => {
				this.trigger(
					'remove',
					new EventData(
						{ username: name },
						null,
						null
					)
				);
			}
		);

		// Initialize the save button event handling.
		$(`#btn-user-save-${name}`).on(
			'click',
			() => {
				this.trigger(
					'save',
					new EventData(
						{ username: name, groups: this.groups.selected },
						null,
						null
					)
				);
			}
		);
	}

	get_element() {
		return $(`#user-list-entry-${this.user.get_user()}`)[0];
	}

	trigger(name, data) {
		$(this.get_element()).trigger(
			`component.userlistentry.${name}`,
			data
		);
	}
}
exports.UserListEntry = UserListEntry;
