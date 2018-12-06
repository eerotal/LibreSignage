var $ = require('jquery');
var UIController = require('ls-uicontrol').UIController;
var UIInput = require('ls-uicontrol').UIInput;
var UIButton = require('ls-uicontrol').UIButton;
var MultiSelect = require('ls-multiselect').MultiSelect;

var StrValidator = require('ls-validator').StrValidator;
var WhitelistValidator = require('ls-validator').WhitelistValidator;
var BlacklistValidator = require('ls-validator').BlacklistValidator;

var APIUI = require('ls-api-ui');
var EditorController = require('./editorcontroller.js').EditorController;
var User = require('ls-user').User;

class EditorView {
	constructor(api) {
		this.api        = api;
		this.controller = new EditorController(api);
		this.ready      = false;

		this.buttons    = null;
		this.inputs     = null;
	}

	async init() {
		let users = null;
		let user = null;

		try {
			users = await User.list_all(this.api);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		user = this.api.get_session().get_user().get_user();

		this.inputs = new UIController({
			name: new UIInput({
				elem: $('#slide-name'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: null,
				attach:  null,
				defer:   () => this.ready,
				mod:     null,
				getter:  e => e.val(),
				setter:  (e, val) => e.val(val),
				clearer: e => e.val('')
			}),
			owner: new UIInput({
				elem:    $('#slide-owner'),
				cond:    () => false,
				enabler: null,
				attach:  null,
				defer:   () => this.ready,
				mod:     null,
				getter:  e => e.val(),
				setter:  (e, val) => e.val(val),
				clearer:  e => e.val('')
			}),
			collaborators: new UIInput({
				elem: new MultiSelect(
					'slide-collab-group',
					'slide-collab',
					[
						new StrValidator(
							{min: 1, max: null, regex: null},
							'', true
						),
						new WhitelistValidator(
							{wl: users},
							"This user doesn't exist."
						),
						new BlacklistValidator(
							{bl: [user]}
						)
					]
				),
				cond: d => (
					d.loaded
					&& d.locked
					&& d.owned
				),
				enabler: (e, s) => s ? e.enabled() : e.disable(),
				attach:  null,
				defer:   () => this.ready,
				mod:     null,
				getter:  e => e.selected,
				setter:  (e, val) => e.set(val),
				clearer: e => e.set([])
			})
		});
		this.buttons = new UIController({
			save: new UIButton({
				elem: $('#slide-save'),
				cond: d => (
					d.loaded
					&& d.locked
					&& (d.owned || d.collaborate)
				),
				enabler: null,
				attach:  { click: () => this.slide_save() },
				defer:   () => this.ready
			}),
			remove: new UIButton({
				elem: $('#slide-remove'),
				cond: d => (
					d.loaded
					&& d.locked
					&& d.owned
				),
				enabler: null,
				attach:  { click: () => this.slide_remove() },
				defer:   () => this.ready
			})
		});
		this.ready = true;

		await this.show_slide('0x1');
		this.update();
	}

	async show_queue(name) {
		try {
			await this.controller.open_queue(name);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
	}

	hide_queue() {
		this.controller.close_queue();
	}

	async show_slide(id) {
		let s = null;
		try {
			await this.controller.open_slide(id);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		s = this.controller.get_slide();

		this.inputs.get('name').set(s.get('name'));
		this.inputs.get('owner').set(s.get('owner'));
		this.inputs.get('collaborators').set(s.get('collaborators'));
	}

	async hide_slide() {
		try {
			await this.controller.close_slide();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
	}

	update() {
		this.inputs.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.buttons.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
	}
}
exports.EditorView = EditorView;
