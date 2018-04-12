/*
*  Functionality for controlling user settings like passwords.
*/

const user_session_row = (who, from) => `
	<tr>
		<td>${who}</td>
		<td>${from}</td>
		<td style="width: 60ypx;">
			<input type="button"
				class="btn btn-danger"
				style="width: 60px;"
				value="End">
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

var USER_SESSION_TABLE = $("#user-session-table");

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

	user_sessions_populate();
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

function user_sessions_populate() {
	api_call(
		API_ENDP.AUTH_GET_SESSIONS,
		null,
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				return;
			}
			USER_SESSION_TABLE.html("");
			for (let d of resp.sessions) {
				USER_SESSION_TABLE.append(
					user_session_row(
						d.who,
						d.from
					)
				);
			}
		}
	);
}

$(document).ready(() => {
	api_init(
		null,	// Use default config.
		() => {
			// Load the current (logged in) user.
			_usr = new User();
			_usr.load(null, user_settings_setup);
		}
	);
});
