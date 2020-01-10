var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');

var UserController = require('./usercontroller.js').UserController;
var UserValidators = require('./uservalidators.js').UserValidators;
var SessionList = require('./components/sessionlist.js').SessionList;
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');
var BaseComponent = require('libresignage/ui/components/BaseComponent');

class UserView extends BaseComponent {
	constructor(api) {
		super();
		this.api = api;
		this.controller = new UserController(api);

		this.init_state({
			ready: false,
			loading: false
		});
	}

	async init() {
		/*
		*  Initialize the view.
		*/
		this.inputs = new UIController({
			username: new UIInput({
				elem: $('#user-name'),
				cond: () => false,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.val(),
				setter: (elem, val) => elem.val(val),
				clearer: elem => elem.val('')
			}),
			groups: new UIInput({
				elem: $('#user-groups'),
				cond: () => false,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.val().replace(/\s/g, ''.split(',')),
				setter: (elem, val) => elem.val(val.join(', ')),
				clearer: elem => elem.val('')
			}),
			password: new UIInput({
				elem: $('#user-pass'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.val(),
				setter: null,
				clearer: elem => elem.val('')
			}),
			password_confirm: new UIInput({
				elem: $('#user-pass-confirm'),
				cond: () => true,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: elem => elem.val(),
				setter: null,
				clearer: elem => elem.val('')
			})
		});

		this.buttons = new UIController({
			save: new UIButton({
				elem: $('#user-save'),
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
				elem: $('#btn-logout-other'),
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
			$('#user-sessions')[0]
		);

		await this.populate();
		this.state('ready', true);
	}

	async populate() {
		/*
		*  Populate the UI with userdata.
		*/
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

	async save_password() {
		/*
		*  Save the modified password.
		*/
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
		this.inputs.get('password').get_elem().trigger('input');
	}

	async logout_other_sessions() {
		/*
		*  Logout other sessions excluding the current one.
		*/
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
exports.UserView = UserView;
