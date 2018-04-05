/*
*  Functionality for controlling user settings like passwords and
*  managing authentication keys.
*/

const user_nokeys_row = () => `
	<tr>
		<td colspan="2"
			class="text-center">
			No keys exist :(
		</td>
	</tr>
`;

const user_keys_row = (key) => `
	<tr>
		<td>${key}</td>
		<td>
			<button type="button"
				class="btn btn-danger"
				onclick="user_settings_key_delete('${key}')">
				Delete
			</button>
		</td>
	</tr>
`;

var USER_NAME = $("#user-name");
var USER_GROUPS = $("#user-groups");
var USER_PASS = $("#user-pass");
var USER_PASS_GRP = $("#user-pass-group");
var USER_PASS_CONFIRM = $("#user-pass-confirm");
var USER_PASS_CONFIRM_GRP = $("#user-pass-confirm-group");
var USER_SAVE = $("#user-save");

var USER_KEYS_TBODY = $("#user-keys-table tbody");

var pass_sel = null;
var _usr = null;

function user_settings_setup() {
	pass_sel = new ValidatorSelector(
		USER_PASS.add(USER_PASS_CONFIRM),
		USER_PASS_GRP.add(USER_PASS_CONFIRM_GRP),
		[
			new StrValidator({
				min: 1,
				max: 10,
				regex: null
			},"The password length is invalid."),
			new EqValidator(
				null,
				"The passwords don't match."
			)
		],
		[
			(sel) => {
				USER_SAVE.attr('disabled', !sel.state());
			}
		]
	);
	USER_NAME.val(_usr.get_name());
	USER_GROUPS.val(_usr.get_groups());
	user_keys_populate();
}

function user_keys_populate() {
	// Populate the authentication keys table.
	USER_KEYS_TBODY.html("");
	if (!_usr.get_keys().length) {
		USER_KEYS_TBODY.html(user_nokeys_row());
	} else {
		for (let k of _usr.get_keys()) {
			USER_KEYS_TBODY.append(user_keys_row(k));
		}
	}
}

function user_settings_save(usr) {
	/*
	*  Save the modified user settings (password etc).
	*/
	if (!_usr) {
		throw new Error("Current user not loaded.");
	}

	// Change password using the API.
	_usr.pass = USER_PASS.val();
	_usr.save((ret) => {
		if (api_handle_disp_error(ret)) {
			return;
		}

		// Empty input boxes.
		USER_PASS.val('');
		USER_PASS_CONFIRM.val('');

		dialog(DIALOG.ALERT,
			'Changes saved',
			'Changes to user settings saved.',
			null);
	});
}

function user_settings_key_generate() {
	/*
	*  Generate a new authentication key for the logged in user.
	*  Wraps User.gen_key() and handles updating the UI.
	*/
	_usr.gen_key((err) => {
		if (api_handle_disp_error(err)) {
			return;
		}
		user_keys_populate();
	});
}

function user_settings_key_delete(key) {
	/*
	*  Delete the authentication key 'key'. Wraps User.rm_key()
	*  and handles updating the UI.
	*/
	_usr.rm_key(key, (err) => {
		if (api_handle_disp_error(err)) {
			return;
		}
		user_keys_populate();
	});
}

api_init(
	null,	// Use default config.
	() => {
		// Load the current (logged in) user.
		_usr = new User();
		_usr.load(null, user_settings_setup);
	}
);
