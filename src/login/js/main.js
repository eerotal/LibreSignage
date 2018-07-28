var $ = require('jquery');
var api = require('ls-api');
var uic = require('ls-uicontrol');
var val = require('ls-validator');
var bootstrap = require('bootstrap');

const LOGIN_LANDING = "/control";
const INPUT_USERNAME = $("#input-user");
const INPUT_PASSWORD = $("#input-pass");
const BTN_LOGIN = $("#btn-login");
const CHECK_PERM = $("#checkbox-perm-session");

var API = null;

var pass_sel = null;
var user_sel = null;

var flag_login_ready = false;
var defer_login_ready = () => { return !flag_login_ready; }

const LOGIN_UI_DEFS = new uic.UIController({
	'INPUT_USERNAME': new uic.UIInput(
		_elem = INPUT_USERNAME,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'keyup': (event) => {
				if (
					event.key == 'Enter'
					&& LOGIN_UI_DEFS.get('INPUT_USERNAME').get().length
					&& LOGIN_UI_DEFS.get('INPUT_PASSWORD').get().length
				) {
					login();
				}
			}
		},
		_defer = defer_login_ready,
		_mod = null,
		_getter = (elem) => { return elem.val(); },
		_setter = null,
		_clear = null
	),
	'INPUT_PASSWORD': new uic.UIInput(
		_elem = INPUT_PASSWORD,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'keyup': (event) => {
				if (
					event.key == 'Enter'
					&& LOGIN_UI_DEFS.get('INPUT_USERNAME').get().length
					&& LOGIN_UI_DEFS.get('INPUT_PASSWORD').get().length
				) {
					login();
				}
			}
		},
		_defer = defer_login_ready,
		_mod = null,
		_getter = (elem) => { return elem.val(); },
		_setter = null,
		_clear = null
	),
	'BTN_LOGIN': new uic.UIButton(
		_elem = BTN_LOGIN,
		_perm = () => { return true; },
		_enabler = null,
		_attach = { 'click': login },
		_defer = defer_login_ready
	)
});

function login_redirect(uri) {
	window.location.assign(uri);
}

function login() {
	API.login(
		INPUT_USERNAME.val(),
		INPUT_PASSWORD.val(),
		CHECK_PERM.is(":checked"),
		(resp) => {
			if (resp.error == API.ERR.API_E_INCORRECT_CREDS) {
				login_redirect("/login?failed=1");
			} else if (resp.error == API.ERR.API_E_OK) {
				if (CHECK_PERM.is(":checked")) {
					login_redirect('/app');
					return;
				}
				login_redirect(LOGIN_LANDING);
			} else {
				API.handle_disp_error(resp.error);
			}
		}
	)
}

$(document).ready(() => {
	API = new api.API(null, () => {
		user_sel = new val.ValidatorSelector(
			INPUT_USERNAME,
			null,
			[new val.StrValidator({
				min: 1,
				max: null,
				regex: null
			}, '')]
		);
		pass_sel = new val.ValidatorSelector(
			INPUT_PASSWORD,
			null,
			[new val.StrValidator({
				min: 1,
				max: null,
				regex: null
			}, '')]
		);
		val_trigger = new val.ValidatorTrigger(
			[user_sel, pass_sel],
			(valid) => {
				LOGIN_UI_DEFS.get('BTN_LOGIN').enabled(valid);
			}
		);

		flag_login_ready = true;
	});
});
