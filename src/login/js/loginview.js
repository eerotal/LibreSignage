/*
*  View object for the Login page.
*/

var $ = require('jquery');
var bootstrap = require('bootstrap');
var APIError = require('ls-api').APIError;
var APIUI = require('ls-api-ui');

var UIController = require('ls-uicontrol').UIController;
var UIInput = require('ls-uicontrol').UIInput;
var UIButton = require('ls-uicontrol').UIButton;

var LoginController = require('./logincontroller.js').LoginController;

class LoginView {
	constructor(api) {
		this.ready = false;
		this.api = api;
		this.controller = new LoginController(api);
	}

	init() {
		/*
		*  Initialize the input controllers etc.
		*/
		this.inputs = new UIController({
			username: new UIInput({
				elem: $('#input-user'),
				cond: () => true,
				enabler: null,
				attach: {
					keyup: async e => {
						if (
							e.key === 'Enter'
							&& this.inputs.get('username').get().length
							&& this.inputs.get('password').get().length
						) { await this.login(); }
						this.update();
					}
				},
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.val(),
				setter: null,
				clearer: null
			}),
			password: new UIInput({
				elem: $('#input-pass'),
				cond: () => true,
				enabler: null,
				attach: {
					keyup: async e => {
						if (
							e.key === 'Enter'
							&& this.inputs.get('username').get().length
							&& this.inputs.get('password').get().length
						) { await this.login(); }
						this.update();
					}
				},
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.val(),
				setter: null,
				clearer: null
			}),
			permanent: new UIInput({
				elem: $('#checkbox-perm-session'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.prop('checked'),
				setter: null,
				clearer: null
			})
		});
		this.buttons = new UIController({
			login: new UIButton({
				elem: $('#btn-login'),
				cond: () => {
					return(
						this.inputs.get('password').get().length
						&& this.inputs.get('username').get().length
					);
				},
				enabler: null,
				attach: { click: async () => { await this.login(); } },
				defer: () => !this.ready
			})
		})

		this.update();
		this.ready = true;
	}

	async login() {
		/*
		*  Login using the credentials in the input fields.
		*/
		try {
			await this.controller.login(
				this.inputs.get('username').get(),
				this.inputs.get('password').get(),
				this.inputs.get('permanent').get()
			);
		} catch (e) {
			if (e instanceof APIError) {
				if (e.response.error === APIError.codes.API_E_INCORRECT_CREDS) {
					window.location.assign('/login?failed=1');
					return;
				} else {
					APIUI.handle_error(e);
					return;
				}
			} else {
				throw e;
			}
		}
		if (this.inputs.get('permanent').get()) {
			window.location.assign('/app');
		} else {
			window.location.assign('/control');
		}
	}

	update() {
		/*
		*  Update the UI elements.
		*/
		this.inputs.all(function() { this.state(); });
		this.buttons.all(function() { this.state(); });
	}
}
exports.LoginView = LoginView;
