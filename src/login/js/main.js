var $ = require('jquery');
var uic = require('ls-uicontrol');
var val = require('ls-validator');
var bootstrap = require('bootstrap');

var APIInterface = require('ls-api').APIInterface;
var APIEndpoints = require('ls-api').APIEndpoints;
var APIUI = require('ls-api-ui');

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
		elem = INPUT_USERNAME,
		perm = () => { return true; },
		enabler = null,
		attach = {
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
		defer = defer_login_ready,
		mod = null,
		getter = (elem) => { return elem.val(); },
		setter = null,
		clearer = null
	),
	'INPUT_PASSWORD': new uic.UIInput(
		elem = INPUT_PASSWORD,
		perm = () => { return true; },
		enabler = null,
		attach = {
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
		defer = defer_login_ready,
		mod = null,
		getter = (elem) => { return elem.val(); },
		setter = null,
		clearer = null
	),
	'BTN_LOGIN': new uic.UIButton(
		elem = BTN_LOGIN,
		perm = () => { return true; },
		enabler = null,
		attach = { 'click': login },
		defer = defer_login_ready
	)
});

function login_redirect(uri) {
	window.location.assign(uri);
}

async function login() {
	try {
		await API.login(
			INPUT_USERNAME.val(),
			INPUT_PASSWORD.val(),
			CHECK_PERM.is(":checked")
		);
	} catch (e) {
		if (e.code === APIError.codes.API_E_INCORRECT_CREDS) {
			login_redirect("/login?failed=1");
			return;
		} else {
			APIUI.handle_error(e);
			return;
		}
	}
	if (CHECK_PERM.is(":checked")) {
		login_redirect('/app');
		return;
	}
	login_redirect(LOGIN_LANDING);
}

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
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
	(val_trigger = new val.ValidatorTrigger(
		[user_sel, pass_sel],
		(valid) => {
			LOGIN_UI_DEFS.get('BTN_LOGIN').enabled(valid);
		}
	)).trigger();
	flag_login_ready = true;	
});
