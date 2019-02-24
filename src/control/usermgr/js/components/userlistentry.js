var $ = require('jquery');
var MultiSelect = require('ls-multiselect').MultiSelect;
var StrValidator = require('ls-validator').StrValidator;

const user_list_entry = (name, password) => `
	<div class="card">
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
				<table>
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
				</div>
			</div>
		</div>
	</div>
`;

class UserListEntry {
	constructor(api, user) {
		this.api = api;
		this.user = user;
	}

	render(where) {
		let pass = this.user.get_password();
		$(where).append(user_list_entry(
			this.user.get_user(),
			pass != null ? pass : '(Hidden)'
		));

		this.groups_multiselect = new MultiSelect(
			`${this.user.get_user()}-groups-group`,
			`${this.user.get_user()}-groups`,
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
		this.groups_multiselect.set(this.user.get_groups());
	}
}
exports.UserListEntry = UserListEntry;
