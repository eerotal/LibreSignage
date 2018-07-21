/*
*  Functionality for controlling user settings like passwords.
*/

var $ = require('jquery');
var api = require('ls-api');
var val = require('ls-validator');
var user = require('ls-user');
var dialog = require('ls-dialog');

var API = null;

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
var usr = null;

function user_settings_setup() {
	pass_sel = new val.ValidatorSelector(
		USER_PASS.add(USER_PASS_CONFIRM),
		USER_PASS_GRP.add(USER_PASS_CONFIRM_GRP),
		[
			new val.StrValidator({
				min: 1,
				max: null,
				regex: null
			}, "The password is too short."),
			new val.StrValidator({
				min: null,
				max: 10,
				regex: null
			}, "The password is too long."),
			new val.EqValidator(
				null,
				"The passwords don't match."
			)
		],
		[
			(sel) => {
				USER_SAVE.prop(
					'disabled',
					!sel.get_state()
				);
			}
		]
	);
	USER_NAME.val(usr.get_name());
	USER_GROUPS.val(usr.get_groups());

	// Setup the sessions tables.
	user_sessions_populate();
	BTN_LOGOUT_OTHER.on("click", user_sessions_logout);
}

function user_sessions_populate() {
	/*
	*  Display the active sessions.
	*/
	API.call(
		API.ENDP.AUTH_GET_SESSIONS,
		null,
		(resp) => {
			if (API.handle_disp_error(resp.error)) {
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
	API.call(
		API.ENDP.AUTH_LOGOUT_OTHER,
		null,
		(resp) => {
			if (API.handle_disp_error(resp.error)) {
				return;
			}
			user_sessions_populate();
		}
	);
}

window.user_settings_save = function() {
	/*
	*  Save the modified user settings (password etc).
	*/
	if (!usr) {
		throw new Error("Current user not loaded.");
	}

	// Change password using the API.
	usr.pass = USER_PASS.val();
	usr.save((ret) => {
		if (API.handle_disp_error(ret)) {
			return;
		}

		// Empty input boxes.
		USER_PASS.val('');
		USER_PASS_CONFIRM.val('');

		dialog.dialog(
			dialog.TYPE.ALERT,
			'Changes saved',
			'Changes to user settings saved.',
			null,
			null
		);
	});
}

$(document).ready(() => {
	API = new api.API(
		null,	// Use default config.
		() => {
			// Load the current (logged in) user.
			usr = new user.User(API);
			usr.load(null, user_settings_setup);
		}
	);
});
