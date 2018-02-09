USERS_TABLE = $('#users-table');

// User table row template.
const usr_table_row = (index, name, groups, pass) => `
	<div class="row usr-table-row" id="usr-row-${name}">
		<div id="usr-index-${name}" class="usr-table-col col-1">
			${index}
		</div>
		<div id="usr-name-${name}" class="usr-table-col col-2">
			${name}
		</div>
		<div id="usr-groups-${name}" class="usr-table-col col-3">
			${groups}
		</div>
		<div id="usr-info-${name}" class="usr-table-col col-3">
			${pass}
		</div>
		<div class="usr-table-col col-3">
			<button type="button"
				role="button"
				class="btn btn-primary"
				data-toggle="collapse"
				data-target="#usr-edit-${name}"
				aria-expanded="false"
				aria-controls="usr-collapse-${name}">

				Edit
			</button>
		</div>
	</div>
	<div class="collapse usr-edit-row" id="usr-edit-${name}">
		<div class="usr-edit-row-container">
			<div class="row usr-edit-input-row">
				<label class="col-3 col-form-label"
					for="usr-name-input-${name}">
					User
				</label>
				<div class="col-9">
					<input id="usr-name-input-${name}"
						type="text"
						class="form-control"
						value="${name}"
						readonly>
					</input>
				</div>
			</div>
			<div class="row usr-edit-input-row">
				<label class="col-3 col-form-label"
					for="usr-groups-input-${name}">
					Groups
				</label>
				<div class="col-9">
					<input id="usr-groups-input-${name}"
						type="text"
						class="form-control"
						value="${groups}">
					</input>
				</div>
			</div>
			<div class="row usr-edit-input-row">
					<div class="col-12 d-flex flex-row justify-content-center">
					<input class="btn btn-primary usr-edit-btn"
						type="submit"
						onclick="usermgr_save('${name}');"
						value="Save">
					</input>
					<input class="btn btn-danger usr-edit-btn"
						type="button"
						onclick="usermgr_remove('${name}');"
						value="Remove">
					</input>
				</div>
			</div>
		</div>
	</div>
`;

function usermgr_assign_new_user_data(user) {
	/*
	*  Assign the edited user data for 'user' from
	*  the user manager UI.
	*/
	var tmp = '';
	var usrs = users_get();
	if (!user_exists(user)) {
		throw new Error("User doesn't exist!");
	}
	for (var u in usrs) {
		if (usrs[u].get_name() == user) {
			tmp = $("#usr-groups-input-" +
				usrs[u].get_name()).val();
			/*
			*  Only allow alphanumerics, underscore,
			*  space and comma in group names.
			*/
			if (tmp.match(/[^A-Za-z0-9_, ]/g)) {
				dialog(DIALOG.ALERT,
					'Invalid user groups ',
					'The user groups specified ' +
					'for ' + user + ' contain ' +
					'invalid characters. Only ' +
					'A-Z, a-z, 0-9 and _ are ' +
					'allowed. Additionally the ' +
					'comma character can be used ' +
					'for separating different ' +
					'groups. Spaces can be used ' +
					'too, but they are removed ' +
					'from the group names when ' +
					'the changes are saved.',
					null);
				return false;
			}
			tmp = tmp.replace(/\s+/g, '');
			usrs[u].groups = tmp.split(',');
			return true;
		}
	}
}

function usermgr_save(name) {
	/*
	*  Save the user 'name'.
	*/
	var usrs = users_get();
	var ret = false;
	for (var u in usrs) {
		if (usrs[u].get_name() == name) {
			ret = usermgr_assign_new_user_data(
					usrs[u].get_name()
			);
			if (!ret) {
				console.error('Failed to save ' +
						'userdata.');
			}
			usrs[u].save((err) => {
				if (err == API_E.LIMITED) {
					dialog(DIALOG.ALERT,
						"Too many groups",
						"You have specified " +
						"too many groups for " +
						"one user.", null);
					return;
				} else if (api_handle_disp_error(err)) {
					return;
				}
				// Update the UI.
				usermgr_make_ui();
				dialog(DIALOG.ALERT,
					'User saved!',
					'User information was ' +
					'successfully saved!',
					null);
			});
			break;
		}
	}

}

function usermgr_remove(name) {
	/*
	*  Remove the user named 'user'.
	*/

	dialog(DIALOG.CONFIRM,
		'Remove user ' + name + '?',
		"Are you really sure you want to remove the " +
		"user " + name + "? All user data for " + name +
		" will be lost and won't be recoverable.",
		(status, val) => {
			var usrs = users_get();
			for (var u in usrs) {
				if (usrs[u].get_name() != name) {
					continue;
				}

				usrs[u].remove((resp) => {
					if (api_handle_disp_error(resp)) {
						return;
					}
					users_load(() => {
						usermgr_make_ui();
					});
				});

				dialog(DIALOG.ALERT,
					'User removed!',
					'User ' + name +
					' successfully removed.',
					null);
				break;
			}
			dialog(DIALOG.ALERT,
				'Failed to remove user!',
				"The user " + name + " wasn't found.",
				null);

		}
	);
}

function usermgr_create() {
	dialog(DIALOG.PROMPT,
		'Create a user',
		'Enter a name for the new user.', (status, val) => {
		if (!status) {
			return;
		}
		api_call(API_ENDP.USER_CREATE,
			{'user': val}, (resp) => {
			if (resp.error == API_E.LIMITED) {
				dialog(DIALOG.ALERT,
					"Can't create user",
					"The maximum number " +
					"of users on the " +
					"server has been " +
					"reached. No more " +
					"users can be created.",
					null);
				return;
			} else if (api_handle_disp_error(resp.error)) {
				return;
			}

			var tmp = new User();
			tmp.set(resp.user.name,
				resp.user.groups,
				null);
			tmp.set_info('Password: ' +
				resp.user.pass);
			users_add(tmp);
			usermgr_make_ui();
		});
	});
}

function usermgr_make_ui() {
	/*
	*  Render the user manager UI.
	*/
	var grps = null;
	var info = null;
	var i = 0;
	var usrs = users_get();
	USERS_TABLE.empty();
	for (var u in usrs) {
		grps = usrs[u].get_groups();
		info = usrs[u].get_info();
		USERS_TABLE.append(usr_table_row(
			i,
			usrs[u].get_name(),
			!grps || !grps.length ? '' : grps.join(', '),
			!info ? '' : info,
		));
		i++;
	}
}

function usermgr_ui_setup() {
	users_load(function() {
		usermgr_make_ui();
	});
}

usermgr_ui_setup();
