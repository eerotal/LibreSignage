/*
*  Functionality for changing user settings via the API on the
*  User Settings page.
*/

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
	$("#user-name").val(_usr.get_name());
	$("#user-groups").val(_usr.get_groups());
	$("#user-pass").attr(
		'maxlength',
		SERVER_LIMITS.PASSWORD_MAX_LEN
	);
	$("#user-pass-confirm").attr(
		'maxlength',
		SERVER_LIMITS.PASSWORD_MAX_LEN
	);
}

function user_settings_save(usr) {
	if (!_usr) {
		throw new Error("Current user not loaded.");
	}

	if (!$("#user-pass").val() && !$("#user-pass-confirm").val()) {
		return;
	}

	if ($("#user-pass").val() == $("#user-pass-confirm").val()) {
		// Change password using the API.
		_usr.pass = $("#user-pass").val();
		_usr.save((ret) => {
			if (api_handle_disp_error(ret)) {
				return;
			}

			// Remove possible mismatch indicators.
			$("#user-pass").removeClass('is-invalid');
			$("label[for=user-pass]").removeClass('is-invalid-label');
			$("#user-pass-confirm").removeClass('is-invalid');
			$("label[for=user-pass-confirm]").removeClass('is-invalid-label');

			// Empty input boxes.
			$("#user-pass").val('');
			$("#user-pass-confirm").val('');

			dialog(DIALOG.ALERT,
				'Changes saved',
				'Changes to user settings saved.',
				null);
		});

	} else {
		// Indicate mismatch in password fields.
		$("#user-pass").addClass('is-invalid');
		$("label[for=user-pass]").addClass('is-invalid-label');
		$("#user-pass-confirm").addClass('is-invalid');
		$("label[for=user-pass-confirm]").addClass('is-invalid-label');
	}
}

api_load_limits(() => {
	user_settings_get_user(user_settings_setup);
});
