var $ = require('jquery');
var bootstrap = require('bootstrap');

var UIController = require('ls-uicontrol').UIController;
var UIButton = require('ls-uicontrol').UIButton;

var StrValidator = require('ls-validator').StrValidator;
var dialog = require('ls-dialog');
var APIUI = require('ls-api-ui');

var UserManagerController = require('./usermanagercontroller.js').UserManagerController;
var UserList = require('./components/userlist.js').UserList;

class UserManagerView {
	constructor(api) {
		this.api = api;
		this.ready = false;

		this.controller = new UserManagerController(api);

		this.buttons = new UIController({
			create: new UIButton({
				elem: $('#btn-create-user'),
				cond: () => true,
				enabler: null,
				attach: { click: () => this.create_user() },
				defer: () => !this.ready
			})
		});

		this.userlist = new UserList(this.api);
	}

	async init() {
		await this.populate();
		this.ready = true;
	}

	create_user() {
		/*
		*  Prompt for a username and create a new user.
		*/
		dialog.dialog(
			dialog.TYPE.PROMPT,
			'Create a new user',
			'Enter a name for the new user.',
			async (status, val) => {
				let user = null;

				if (!status) { return; }
				try {
					user = await this.controller.create_user(val);
				} catch (e) {
					APIUI.handle_error(e);
					return;
				}
			},
			[
				new StrValidator({
					min: 1,
					max: null,
					regex: null
				}, 'The username is too short.'),
				new StrValidator({
					min: null,
					max: this.api.limits.USERNAME_MAX_LEN,
					regex: null
				}, 'The username is too long.'),
				new StrValidator({
					min: null,
					max: null,
					regex: /^[A-Za-z0-9_]*$/
				}, 'The username contains invalid characters.')
			]
		)
	}

	async populate() {
		// Fetch userdata.
		try {
			this.userlist.set_user_data(
				await this.controller.get_users()
			);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		// Populate the UI.
		this.userlist.render($('#users-table')[0]);
	}
}
exports.UserManagerView = UserManagerView;
