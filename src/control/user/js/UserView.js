var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');
var BaseComponent = require('libresignage/ui/components/BaseComponent');
var UserController = require('./UserController.js');
var UserValidators = require('./UserValidators.js');
var SessionList = require('./components/SessionList.js');

/**
* View class for the User Settings page.
*/
class UserView extends BaseComponent {
	/**
	* Construct a new UserView object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		super();
		this.api = api;
		this.controller = new UserController(api);

		this.init_state({
			ready: false,
			loading: false
		});
	}

	/**
	* Initialize the view.
	*/
	async init() {
		this.inputs = new UIController({
			username: new UIInput({
				elem: document.querySelector('#user-name'),
				cond: () => false,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.value,
				setter: (elem, val) => elem.value = val,
				clearer: elem => elem.value = ''
			}),
			groups: new UIInput({
				elem: document.querySelector('#user-groups'),
				cond: () => false,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.value.replace(/\s/g, ''.split(',')),
				setter: (elem, val) => elem.value = val.join(', '),
				clearer: elem => elem.value = ''
			}),
			password: new UIInput({
				elem: document.querySelector('#user-pass'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.value,
				setter: null,
				clearer: elem => elem.value = ''
			}),
			password_confirm: new UIInput({
				elem: document.querySelector('#user-pass-confirm'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.value,
				setter: null,
				clearer: elem => elem.value = ''
			})
		});

		this.buttons = new UIController({
			save: new UIButton({
				elem: document.querySelector('#user-save'),
				cond: () => this.validators.get_state(),
				enabler: null,
				attach: {
					click: async () => {
						this.state('loading', true);
						await this.save_password();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			logout_other: new UIButton({
				elem: document.querySelector('#btn-logout-other'),
				cond: () => true,
				enabler: null,
				attach: {
					click: async () => {
						this.state('loading', true);
						await this.logout_other_sessions();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			})
		});

		this.validators = new UserValidators();
		this.validators.create_trigger(() => this.update());

		this.sessionlist = new SessionList(
			this.api,
			document.querySelector('#user-sessions')
		);

		await this.populate();
		this.state('ready', true);
	}

	/**
	* Populate the UI with userdata.
	*/
	async populate() {
		this.inputs.get('username').set(
			this.controller.get_user().get_user()
		);
		this.inputs.get('groups').set(
			this.controller.get_user().get_groups()
		);

		// Fetch the SessionList data and render it.
		try {
			await this.sessionlist.fetch();
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}
		this.sessionlist.render();
	}

	/**
	* Save the modified password.
	*/
	async save_password() {
		try {
			await this.controller.save_password(
				this.inputs.get('password').get()
			);
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}

		this.inputs.get('password').clear();
		this.inputs.get('password_confirm').clear();

		// Trigger ValidatorTrigger.
		this.inputs
			.get('password')
			.get_elem()
			.dispatchEvent(new Event('input'));
	}

	/**
	* Logout other sessions excluding the current one.
	*/
	async logout_other_sessions() {
		try {
			await this.controller.logout_other_sessions();
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}
		await this.populate();
	}

	update() {
		this.buttons.all(function() { this.state(); })
	}
}
module.exports = UserView;
