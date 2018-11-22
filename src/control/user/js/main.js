/*
*  Functionality for controlling user settings like passwords.
*/

var $ = require('jquery');
var APIInterface = require('ls-api').APIInterface;
var val = require('ls-validator');
var User = require('ls-user').User;
var dialog = require('ls-dialog');
var util = require('ls-util');
var uic = require('ls-uicontrol');
var assert = require('ls-assert').assert;

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
		elem = USER_NAME,
		perm = () => { return true; },
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, u) => { return elem.val() != u.data.user(); },
		getter = (elem) => { return elem.val(); },
		setter = (elem, u) => { elem.val(u.data.user); },
		clearer = (elem) => { elem.val(''); }
	),
	'USER_GROUPS': new uic.UIInput(
		elem = USER_GROUPS,
		perm = () => { return true; },
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, u) => {
			let tmp = elem.val().replace(/\s/g, '').split(',');
			return !sets_eq(tmp, u.data.groups);
		},
		getter = (elem) => {
			return elem.val().replace(/\s/g, '').split(',');
		},
		setter = (elem, u) => { elem.val(u.data.groups.join()); },
		clearer = (elem) => { elem.val(''); }
	),
	'USER_PASS': new uic.UIInput(
		elem = USER_PASS,
		perm = () => { return true; },
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, u) => { return elem.val().length != 0; },
		getter = (elem) => { return elem.val(); },
		setter = null,
		clearer = (elem) => { elem.val(''); }
	),
	'USER_PASS_CONFIRM': new uic.UIInput(
		elem = USER_PASS_CONFIRM,
		perm = () => { return true; },
		enabler = null,
		attach = null,
		defer = null,
		mod = (elem, u) => { return elem.val().length != 0; },
		getter = (elem) => { return elem.val(); },
		setter = null,
		clearer = (elem) => { elem.val(''); }
	),
	'USER_SAVE': new uic.UIButton(
		elem = USER_SAVE,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': user_settings_save
		},
		defer = defer_user_ready
	),
	'BTN_LOGOUT_OTHER': new uic.UIButton(
		elem = BTN_LOGOUT_OTHER,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': user_sessions_logout
		},
		defer = defer_user_ready
	)
});

async function user_settings_setup() {
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
				{},
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

	// Populate user data.
	usr = new User(API);
	await user_data_populate();

	flag_user_ready = true;
}

async function user_data_populate() {
	// Populate user data.
	try {
		await usr.load(null);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	USER_UI_DEFS.all(function(data) { this.set(data)}, usr, 'input');

	// Populate the active sessions table.
	USER_SESSIONS.html("");
	for (let d of usr.data.sessions) {
		USER_SESSIONS.append(
			user_session_row(
				d.who,
				d.from,
				d.created*1000,
				d.id === API.config.session.data.id
			)
		);
	}
}

async function user_sessions_logout() {
	/*
	*  Event handler for the 'Logout other sessions' button.
	*/
	try {
		await API.call(APIEndpoints.AUTH_LOGOUT_OTHER, null);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	await user_data_populate();
}

async function user_settings_save() {
	/*
	*  Save modified user settings.
	*/
	assert(usr != null, "Current user not loaded.");

	usr.data.pass = USER_UI_DEFS.get('USER_PASS').get();
	try {
		await usr.save();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

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
}

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
	}
	await user_settings_setup();
});
