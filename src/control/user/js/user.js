/*
*  Functionality for controlling user settings like passwords.
*/

const user_session_row = (who, from, created, cur) => `
<tr><td>
	<table class="user-session-row">
		<tr>
			<th class="text-right">Name:</th>
			<td>
				${who}
			</td>
		</tr>
		<tr>
			<th class="text-right">IP:</th>
			<td>
				${from}
			</td>
		</tr>
		<tr>
			<th class="text-right">Renewed:</th>
			<td>
				${new Date(created).toUTCString()}
			</td>
		</tr>
		<tr>
			<th class="text-right">Your session:</th>
			<td>
				<span style="color: green;">
					${cur ? "Yes" : ""}
				</span>
				<span style="color: red;">
					${cur ? "" : "No"}
				</span>
			</td>
		</tr>
</table></td>
`;

var USER_NAME = $("#user-name");
var USER_GROUPS = $("#user-groups");
var USER_PASS = $("#user-pass");
var USER_PASS_GRP = $("#user-pass-group");
var USER_PASS_CONFIRM = $("#user-pass-confirm");
var USER_PASS_CONFIRM_GRP = $("#user-pass-confirm-group");
var USER_SAVE = $("#user-save");

var USER_SESSIONS = $("#user-sessions");
var BTN_LOGOUT_OTHER = $("#btn-logout-other");

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

	// Setup the sessions tables.
	user_sessions_populate();
	BTN_LOGOUT_OTHER.on("click", user_sessions_logout);
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
	/*
	*  Display the active sessions.
	*/
	api_call(
		API_ENDP.AUTH_GET_SESSIONS,
		null,
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				return;
			}
			USER_SESSIONS.html("");
			for (let d of resp.sessions) {
				USER_SESSIONS.append(
					user_session_row(
						d.who,
						d.from,
						d.created*1000,
						d.current
					)
				);
			}
		}
	);
}

function user_sessions_logout() {
	/*
	*  Event handler for the 'Logout other sessions' button.
	*/
	api_call(
		API_ENDP.AUTH_LOGOUT_OTHER,
		null,
		(resp) => {
			if (api_handle_disp_error(resp.error)) {
				return;
			}
			user_sessions_populate();
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
