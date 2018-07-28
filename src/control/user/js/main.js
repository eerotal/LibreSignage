/*
*  Functionality for controlling user settings like passwords.
*/

var $ = require('jquery');
var api = require('ls-api');
var val = require('ls-validator');
var user = require('ls-user');
var dialog = require('ls-dialog');
var util = require('ls-util');
var uic = require('ls-uicontrol');

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

var flag_user_ready = false;
var defer_user_ready = () => { return !flag_user_ready; }

const USER_UI_DEFS = new uic.UIController({
	'USER_NAME': new uic.UIInput(
		_elem = USER_NAME,
		_perm = () => { return true; },
		_enabler = null,
		_mod = (elem, u) => { return elem.val() != u.get_name(); },
		_getter = (elem) => { return elem.val(); },
		_setter = (elem, u) => { elem.val(u.get_name()); },
		_clear = (elem) => { elem.val(''); }
	),
	'USER_GROUPS': new uic.UIInput(
		_elem = USER_GROUPS,
		_perm = () => { return true; },
		_enabler = null,
		_mod = (elem, u) => {
			let tmp = elem.val().replace(/\s/g, '').split(',');
			return !sets_eq(tmp, u.get_groups());
		},
		_getter = (elem) => {
			return elem.val().replace(/\s/g, '').split(',');
		},
		_setter = (elem, u) => { elem.val(u.get_groups().join()); },
		_clear = (elem) => { elem.val(''); }
	),
	'USER_PASS': new uic.UIInput(
		_elem = USER_PASS,
		_perm = () => { return true; },
		_enabler = null,
		_mod = (elem, u) => { return elem.val().length != 0; },
		_getter = (elem) => { return elem.val(); },
		_setter = () => {},
		_clear = (elem) => { elem.val(''); }
	),
	'USER_PASS_CONFIRM': new uic.UIInput(
		_elem = USER_PASS_CONFIRM,
		_perm = () => { return true; },
		_enabler = null,
		_mod = (elem, u) => { return elem.val().length != 0; },
		_getter = (elem) => { return elem.val(); },
		_setter = () => {},
		_clear = (elem) => { elem.val(''); }
	),
	'USER_SAVE': new uic.UIButton(
		_elem = USER_SAVE,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'click': user_settings_save
		},
		_defer = defer_user_ready
	),
	'BTN_LOGOUT_OTHER': new uic.UIButton(
		_elem = BTN_LOGOUT_OTHER,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'click': user_sessions_logout
		},
		_defer = defer_user_ready
	)
});

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

	USER_UI_DEFS.all(function(data) { this.set(data)}, usr, 'input');

	// Setup the sessions table.
	user_sessions_populate();
	flag_user_ready = true;
}

function user_sessions_populate() {
	/*
	*  Display the active sessions.
	*/
	API.call(
		API.ENDP.AUTH_GET_SESSIONS,
		null,
		(resp) => {
			if (API.handle_disp_error(resp.error)) { return; }
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
			if (API.handle_disp_error(resp.error)) { return; }
			user_sessions_populate();
		}
	);
}

function user_settings_save() {
	/*
	*  Save the modified user settings (password etc).
	*/
	if (!usr) { throw new Error("Current user not loaded."); }

	// Change password using the API.
	usr.pass = USER_UI_DEFS.get('USER_PASS').get();
	usr.save((ret) => {
		if (API.handle_disp_error(ret)) { return; }

		// Empty input boxes.
		USER_UI_DEFS.get('USER_PASS').clear();
		USER_UI_DEFS.get('USER_PASS_CONFIRM').clear();

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
