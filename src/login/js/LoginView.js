var bootstrap = require('bootstrap');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

var LoginController = require('./LoginController');
var Util = require('libresignage/util/Util');
var APIError = require('libresignage/api/APIError');
var HTTPStatus = require('libresignage/api/HTTPStatus');
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');

/**
* View class for the Login page.
*/
class LoginView {
	/**
	* Construct a new LoginView object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.ready = false;
		this.api = api;
		this.controller = new LoginController(api);
	}

	/**
	* Initialize the view.
	*/
	init() {
		this.inputs = new UIController({
			username: new UIInput({
				elem: document.querySelector('#input-user'),
				cond: () => true,
				enabler: null,
				attach: {
					keyup: async e => {
						if (
							e.key === 'Enter'
							&& this.inputs.get('username').get().length
						) { await this.login(); }
						this.update();
					}
				},
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.value,
				setter: null,
				clearer: null
			}),
			password: new UIInput({
				elem: document.querySelector('#input-pass'),
				cond: () => true,
				enabler: null,
				attach: {
					keyup: async e => {
						if (
							e.key === 'Enter'
							&& this.inputs.get('username').get().length
						) { await this.login(); }
						this.update();
					}
				},
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.value,
				setter: null,
				clearer: null
			}),
			permanent: new UIInput({
				elem: document.querySelector('#checkbox-perm-session'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready,
				mod: null,
				getter: elem => elem.checked,
				setter: null,
				clearer: null
			})
		});
		this.buttons = new UIController({
			login: new UIButton({
				elem: document.querySelector('#btn-login'),
				cond: () => this.inputs.get('username').get().length,
				enabler: null,
				attach: { click: async () => { await this.login(); } },
				defer: () => !this.ready
			})
		})

		this.update();
		this.ready = true;
	}

	/**
	* Login using credentials from the input fields.
	*
	* @throws {APIError} If login via the API fails for a reason other than
	*                    invalid credentials.
	*/
	async login() {
		let query = Util.get_GET_parameters();
		try {
			await this.controller.login(
				this.inputs.get('username').get(),
				this.inputs.get('password').get(),
				this.inputs.get('permanent').get()
			);
		} catch (e) {
			if (e instanceof APIError) {
				if (e.is(HTTPStatus.UNAUTHORIZED)) {
					query.failed = '1';
					window.location.assign(`/login?${Util.querify(query)}`);
					return;
				} else {
					new APIErrorDialog(e);
					return;
				}
			} else {
				throw e;
			}
		}

		/*
		* Redirect the user
		*  * to the originally requested URL if the query
		*    parameter 'redir' is set.
		*  * to '/app' if a permanent session is made.
		*  * to '/control' otherwise.
		*/
		if ('redir' in query) {
			window.location.assign(decodeURIComponent(query.redir));
		} else {
			if (this.inputs.get('permanent').get()) {
				window.location.assign('/app');
			} else {
				window.location.assign('/control');
			}
		}
	}

	/**
	* Update UI elements.
	*/
	update() {
		this.inputs.all(function() { this.state(); });
		this.buttons.all(function() { this.state(); });
	}
}
module.exports = LoginView;
