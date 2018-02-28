/*
*  Functionality for changing user settings via the API on the
*  User Settings page.
*/

var USER_NAME = $("#user-name");
var USER_GROUPS = $("#user-groups");
var USER_PASS = $("#user-pass");
var USER_PASS_GRP = $("#user-pass-group");
var USER_PASS_CONFIRM = $("#user-pass-confirm");
var USER_PASS_CONFIRM_GRP = $("#user-pass-confirm-group");
var USER_SAVE = $("#user-save");

var pass_sel = null;
var _usr = null;

function user_settings_get_user(ready_callback) {
	api_call(API_ENDP.USER_GET_CURRENT, null, (resp) => {
		if (resp.error) {
			throw new Error("API error while loding " +
					"current user data.");
		}
		_usr = new User();
		_usr.set(resp.user.user,
			resp.user.groups,
			null);
		if (ready_callback) {
			ready_callback();
		}
	});
}

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
}

function user_settings_save(usr) {
	if (!_usr) {
		throw new Error("Current user not loaded.");
	}

	if (!pass_sel.state()) { return; }

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

api_init(() => {
	user_settings_get_user(user_settings_setup);
});
