var $ = require('jquery');
var bootstrap = require('bootstrap');
var BaseView = require('ls-baseview').BaseView;
var UIController = require('ls-uicontrol').UIController;
var UIInput = require('ls-uicontrol').UIInput;
var UIButton = require('ls-uicontrol').UIButton;
var UIStatic = require('ls-uicontrol').UIStatic;
var MultiSelect = require('ls-multiselect').MultiSelect;
var DropConfirm = require('ls-dropconfirm').DropConfirm;
var DropSelect = require('ls-dropselect').DropSelect;
var Popup = require('ls-popup').Popup;

var StrValidator = require('ls-validator').StrValidator;
var WhitelistValidator = require('ls-validator').WhitelistValidator;
var BlacklistValidator = require('ls-validator').BlacklistValidator;

var ShortcutController = require('ls-shortcut').ShortcutController;
var Shortcut = require('ls-shortcut').Shortcut;

var EditorController = require('./editorcontroller.js').EditorController;
var EditorValidators = require('./editorvalidators.js').EditorValidators;

var APIError = require('ls-api').APIError;
var APIUI = require('ls-api-ui');
var User = require('ls-user').User;
var Queue = require('ls-queue').Queue;
var MarkupError = require('ls-markup').err.MarkupError;

var dialog = require('ls-dialog');
var util = require('ls-util');
var ace_range = ace.require('ace/range');

var AssetUploader = require(
	'./components/assetuploader/assetuploader.js'
).AssetUploader;
var Timeline = require(
	'./components/timeline/timeline.js'
).Timeline;
var Preview = require(
	'./components/preview/preview.js'
).Preview;
var QueueSelector = require(
	'./components/queueselector/queueselector.js'
).QueueSelector;

class EditorView extends BaseView {
	constructor(api) {
		super();

		this.api        = api;
		this.controller = new EditorController(api);

		this.buttons    = null;
		this.inputs     = null;
		this.statics    = null;

		this.editor     = null;
		this.timeline   = null;
		this.preview    = null;

		this.error_id   = null;
	}

	async init() {
		let users = null;
		let user = null;

		try {
			await this.controller.init();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		try {
			users = await User.list_all(this.api);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		user = this.api.get_session().get_user().get_user();

		// Define UI element controllers for the editor.
		this.inputs = new UIController({
			name: new UIInput({
				elem: $('#slide-name'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => e.val(val),
				clearer: e => e.val('')
			}),
			owner: new UIInput({
				elem: $('#slide-owner'),
				cond: () => false,
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => e.val(val),
				clearer: e => e.val('')
			}),
			collaborators: new UIInput({
				elem: new MultiSelect(
					'slide-collab-group',
					'slide-collab',
					[
						new StrValidator(
							{ min: 1, max: null, regex: null },
							'', true
						),
						new WhitelistValidator(
							{ wl: users },
							"This user doesn't exist."
						),
						new BlacklistValidator(
							{ bl: [user] }
						)
					],
					{
						nodups: true,
						maxopts: this.api.limits.SLIDE_MAX_COLLAB
					}
				),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: (e, s) => s ? e.enable() : e.disable(),
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.selected,
				setter: (e, val) => e.set(val),
				clearer: e => e.set([])
			}),
			duration: new UIInput({
				elem: $('#slide-duration'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => parseFloat(e.val(), 10)*1000,
				setter: (e, val) => e.val(val/1000),
				clearer: e => e.val('')
			}),
			index: new UIInput({
				elem: $('#slide-index'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => parseInt(e.val(), 10),
				setter: (e, val) => e.val(val),
				clearer: e => e.val('')
			}),
			animation: new UIInput({
				elem: $('#slide-animation'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => parseInt(e.val(), 10),
				setter: (e, val) => e.val(val),
				clearer: e => e.val('')
			}),
			schedule_enable: new UIInput({
				elem: $('#slide-schedule-enable'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: { change: () => this.update() },
				defer: () => !this.state('ready'),
				mod: null,
				getter: e => e.prop('checked'),
				setter: (e, val) => e.prop('checked', val),
				clearer: e => e.prop('checked', false)
			}),
			schedule_date_start: new UIInput({
				elem: $('#slide-sched-date-s'),
				cond: d => (
					this.inputs.get('schedule_enable').get()
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => {
					e.val(util.tstamp_to_datetime(val)[0]);
				},
				clearer: e => e.val('')
			}),
			schedule_time_start: new UIInput({
				elem: $('#slide-sched-time-s'),
				cond: d => (
					this.inputs.get('schedule_enable').get()
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => {
					e.val(util.tstamp_to_datetime(val)[1]);
				},
				clearer: e => e.val('')
			}),
			schedule_date_end: new UIInput({
				elem: $('#slide-sched-date-e'),
				cond: d => (
					this.inputs.get('schedule_enable').get()
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => {
					e.val(util.tstamp_to_datetime(val)[0]);
				},
				clearer: e => e.val('')
			}),
			schedule_time_end: new UIInput({
				elem: $('#slide-sched-time-e'),
				cond: d => (
					this.inputs.get('schedule_enable').get()
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.val(),
				setter: (e, val) => {
					e.val(util.tstamp_to_datetime(val)[1]);
				},
				clearer: e => e.val('')
			}),
			editor: new UIInput({
				elem: $('#slide-input'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: (e, s) => this.editor.setReadOnly(!s),
				attach: { 'keyup': () => this.render_preview() },
				defer: () => !this.state('ready'),
				mod: null,
				getter: e => this.editor.getValue(),
				setter: (e, val) => {
					this.editor.setValue(val);
					this.editor.clearSelection();
				},
				clearer: e => {
					this.editor.setValue('');
					this.editor.clearSelection();
				}
			}),
			enable: new UIInput({
				elem: $('#slide-enable'),
				cond: d => (
					!this.inputs.get('schedule_enable').get()
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: null,
				mod: null,
				getter: e => e.prop('checked'),
				setter: (e, val) => e.prop('checked', val),
				clearer: e => e.prop('checked', false)
			})
		});
		this.statics = new UIController({
			label_readonly: new UIStatic({
				elem: $('#slide-label-readonly'),
				cond: d => (
					d.slide.loaded
					&& !d.slide.locked
					&& !d.slide.owned
					&& !d.slide.collaborate
				),
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: null,
				getter: null,
				setter: null
			}),
			label_edited: new UIStatic({
				elem: $('#slide-label-edited'),
				cond: d => (
					d.slide.loaded
					&& !d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: null,
				getter: null,
				setter: null
			}),
			label_collaborate: new UIStatic({
				elem: $('#slide-label-collaborate'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.collaborate
				),
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: null,
				getter: null,
				setter: null
			}),
			label_no_quota: new UIStatic({
				elem: $('#slide-label-no-quota'),
				cond: d => !d.quota.slides,
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: null,
				getter: null,
				setter: null
			}),
			label_editor_error: new UIStatic({
				elem: $('#slide-label-editor-error'),
				cond: () => true,
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: null,
				getter: null,
				setter: null
			}),
		});
		this.buttons = new UIController({
			timeline: new UIStatic({
				elem: $('#timeline'),
				cond: () => true,
				enabler: null,
				attach: {
					'component.timeline.click': async (e, data) => {
						this.state('loading', true);
						try {
							await this.show_slide(data.get('id'));
						} catch (e) {
							APIUI.handle_error(e);
							data.except();
							return;
						}
						this.state('loading', false);
						data.then();
					}
				},
				defer: () => !this.state('ready') || this.state('loading'),
				getter: null,
				setter: null
			}),
			queueselector: new UIStatic({
				elem: $('#queueselector'),
				cond: () => true,
				enabler: null,
				attach: {
					'component.queueselector.select': async (e, data) => {
						this.state('loading', true);
						try {
							await this.show_queue(data.get('queue'));
						} catch (e) {
							APIUI.handle_error(e);
							data.except();
							return;
						}
						this.state('loading', false);
						data.then();
					},
					'component.queueselector.create': async (e, data) => {
						this.state('loading', true);
						try {
							await this.create_queue(data.get('queue'));
						} catch (e) {
							APIUI.handle_error(e);
							data.except();
							return;
						}
						this.state('loading', false);
						data.then();
					},
					'component.queueselector.view': (e, data) => {
						this.view_queue();
					},
					'component.queueselector.remove': async (e, data) => {
						this.state('loading', true);
						try {
							await this.remove_queue();
						} catch (e) {
							APIUI.handle_error(e);
							data.except();
							return;
						}
						this.state('loading', false);
						data.then();
					},
					'component.queueselector.deselect': (e, data) => {
						data.then();
					}
				},
				defer: () => !this.state('ready') || this.state('loading'),
				getter: null,
				setter: null
			}),
			new: new UIButton({
				elem: $('#btn-slide-new'),
				cond: d => (
					d.quota.slides
					&& d.queue.loaded
				),
				enabler: null,
				attach: {
					click: async () => {
						this.state('loading', true);
						await this.new_slide();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			save: new UIButton({
				elem: $('#btn-slide-save'),
				cond: d => (
					this.validators.get_state()
					&& (
						(
							d.slide.saved
							&& d.slide.loaded
							&& d.slide.locked
							&& (d.slide.owned || d.slide.collaborate)
						) || (
							d.slide.loaded
							&& !d.slide.saved
							&& d.quota.slides
						)
					)
				),
				enabler: null,
				attach: {
					click: async () => {
						this.state('loading', true);
						await this.save_slide();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			duplicate: new UIButton({
				elem: $('#btn-slide-duplicate'),
				cond: d => (
					d.quota.slides
					&& d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: {
					click: async () => {
						this.state('loading', true);
						await this.duplicate_slide();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			preview: new UIButton({
				elem: $('#btn-slide-preview'),
				cond: d => d.slide.loaded,
				enabler: null,
				attach: {
					click: () => this.preview_slide()
				},
				defer: () => !this.state('ready')
			}),
			move: new UIButton({
				elem: $('#btn-slide-move'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: (elem, s) => {
					elem.find('.dropselect-open').prop('disabled', !s);
				},
				attach: {
					'component.dropselect.show': async () => {
						this.state('loading', true);
						await this.update_move_slide_options();
						this.state('loading', false);
					},
					'component.dropselect.select': async (e, data) => {
						this.state('loading', true);
						try {
							await this.move_slide(data.get('option'));
						} catch (e) {
							APIUI.handle_error(e);
							data.except();
							return;
						}
						this.state('loading', false);
						data.then();
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			remove: new UIButton({
				elem: $('#btn-slide-remove'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: (elem, s) => {
					elem.find('.dropconfirm-open').prop('disabled', !s);
				},
				attach: {
					'component.dropconfirm.confirm': async () => {
						this.state('loading', true);
						await this.remove_slide();
						this.state('loading', false);
					}
				},
				defer: () => !this.state('ready') || this.state('loading')
			}),
			quick_help: new UIButton({
				elem: $('#btn-quick-help'),
				cond: d => true,
				enabler: null,
				attach: {
					click: () => this.quick_help.visible(true)
				},
				defer: () => !this.state('ready')
			}),
			add_media: new UIButton({
				elem: $('#btn-add-media'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: {
					click: () => {
						this.asset_uploader.show(
							this.controller.get_slide()
						);
					}
				},
				defer: () => !this.state('ready')
			}),
			preview_16x9: new UIButton({
				elem: $('#btn-preview-ratio-16x9'),
				cond: () => this.preview.get_ratio() !== '16x9',
				enabler: null,
				attach: {
					click: () => this.set_preview_ratio('16x9')
				},
				defer: () => !this.state('ready')
			}),
			preview_4x3: new UIButton({
				elem: $('#btn-preview-ratio-4x3'),
				cond: () => this.preview.get_ratio() !== '4x3',
				enabler: null,
				attach: {
					click: () => this.set_preview_ratio('4x3')
				},
				defer: () => !this.state('ready')
			})
		});

		// Define shortcuts for the editor.
		this.shortcuts = new ShortcutController([
			new Shortcut({ // Control + Alt + n => New slide
				keys: ['Control', 'Alt', 'n'],
				hook: () => {
					let elem = this.buttons.get('new').get_elem();
					if (!elem.prop('disabled')) {
						elem.trigger('click');
					}
				},
				defer: () => !this.state('ready')
			}),
			new Shortcut({ // Control + s => Save slide
				keys: ['Control', 's'],
				hook: () => {
					let elem = this.buttons.get('save').get_elem();
					if (!elem.prop('disabled')) {
						elem.trigger('click');
					}
				},
				defer: () => !this.state('ready')
			}),
			new Shortcut({ // Control + d => Duplicate slide
				keys: ['Control', 'd'],
				hook: () => {
					let elem = this.buttons.get('duplicate').get_elem();
					if (!elem.prop('disabled')) {
						elem.trigger('click');
					}
				},
				defer: () => !this.state('ready')
			}),
			new Shortcut({ // Control + p => Preview slide
				keys: ['Control', 'p'],
				hook: () => {
					let elem = this.buttons.get('preview').get_elem();
					if (!elem.prop('disabled')) {
						elem.trigger('click');
					}
				},
				defer: () => !this.state('ready')
			})
		])

		// Markup editor.
		this.editor = ace.edit('slide-input');
		this.editor.setTheme('ace/theme/dawn');
		this.editor.blockScrolling = Infinity;

		// Queue selector.
		this.queueselector = new QueueSelector('queueselector', this.api);
		await this.queueselector.init();

		// Queue timeline.
		this.timeline = new Timeline('timeline');

		// Live slide preview.
		this.preview = new Preview('preview');
		await this.preview.init();

		// Slide remove DropConfirm.
		this.remove = new DropConfirm($('#btn-slide-remove')[0]);
		this.remove.set_button_html('<i class="fas fa-trash-alt"></i>');
		this.remove.set_content_html('Remove slide?');

		// Slide move DropSelect.
		this.move = new DropSelect($('#btn-slide-move')[0]);
		this.move.set_button_html(
			'<i class="fas fa-arrow-circle-right"></i>'
		);

		// Quick help popup.
		this.quick_help = new Popup($('#quick-help')[0]);

		// Asset uploader popup.
		this.asset_uploader = new AssetUploader(
			$('#asset-uploader')[0],
			this.api
		);

		/*
		*  Initialize the input validators. All input validators except
		*  the validators for the collaborators MultiSelect are defined
		*  in src/control/editor/js/editorvalidators.js. This is because
		*  the MultiSelect component has it's own way of handling input
		*  validators. These validators are defined in the MultiSelect
		*  constructor call instead.
		*
		*  A trigger hook is also added so that the editor state is
		*  updated every time a change in the validation state occurs.
		*  Note that this doesn't need to happen for the collaborators
		*  MultiSelect since it won't allow adding invalid selections
		*  in the first place.
		*/
		this.validators = new EditorValidators(this.api);
		this.validators.create_trigger(() => this.update())

		// Make the initial state of the editor more predictable.
		this.hide_queue();

		window.addEventListener('beforeunload', (e) => {
			if (
				this.controller.get_state().slide.loaded
				&& this.is_slide_modified()
			) {
				e.returnValue = "The editor contains unsaved changes. " +
					"The changes will be lost if you don't save " +
					"them before leaving. Continue anyway?";
				return e.returnValue;
			}
		});

		this.update();
		this.state('ready', true);
	}

	async confirm_slide_hide() {
		return new Promise((resolve, reject) => {
			if (
				this.controller.get_state().slide.loaded
				&& this.is_slide_modified()
			) {
				dialog.dialog(
					dialog.TYPE.CONFIRM,
					"Unsaved changes",
					"The editor contains unsaved changes. The changes " +
					"will be lost if you don't save them before " +
					"continuing. Continue anyway?",
					(status, val) => resolve(status),
				);
			} else { resolve(true); }
		});
	}

	async show_queue(name) {
		/*
		*  Show the queue 'name'. If a slide is already loaded and it
		*  has unsaved changes, the user is prompted for confirmation
		*  before changing the queue.
		*/
		if (!(await this.confirm_slide_hide())) { return; }
		await this.hide_queue();
		await this.controller.open_queue(name);
		await this.timeline.show_queue(this.controller.get_queue());
		this.update();
	}

	async hide_queue() {
		/*
		*  Hide the current queue.
		*/
		await this.hide_slide();
		this.timeline.hide_queue();
		this.controller.close_queue();
		this.update();
	}

	async create_queue(queue) {
		/*
		*  Create a new queue.
		*/
		await this.controller.create_queue(queue);
	}

	view_queue() {
		window.open(`/app/?q=${this.controller.get_queue().get_name()}`);
	}

	async remove_queue() {
		/*
		*  Remove and hide the current queue.
		*/
		await this.hide_slide();
		await this.controller.remove_queue();
		await this.hide_queue();
	}

	async show_slide(id) {
		/*
		*  Show the slide 'id'. If id == null, the current
		*  loaded slide from the EditorController is used.
		*  If a slide is already loaded and it has unsaved
		*  changes, the user is prompted for confirmation
		*  before changing the slide.
		*/
		let s = null;
		if (id != null) {
			// Only confirm changing the slide if id != null.
			if (!(await this.confirm_slide_hide())) { return; }
			await this.controller.open_slide(id);
		}
		s = this.controller.get_slide();

		this.inputs.get('name').set(s.get('name'));
		this.inputs.get('owner').set(s.get('owner'));
		this.inputs.get('collaborators').set(s.get('collaborators'));
		this.inputs.get('duration').set(s.get('duration'));
		this.inputs.get('index').set(s.get('index'));
		this.inputs.get('animation').set(s.get('animation'))
		this.inputs.get('schedule_enable').set(s.get('sched'))
		this.inputs.get('schedule_date_start').set(s.get('sched_t_s'));
		this.inputs.get('schedule_time_start').set(s.get('sched_t_s'));
		this.inputs.get('schedule_date_end').set(s.get('sched_t_e'));
		this.inputs.get('schedule_time_end').set(s.get('sched_t_e'));
		this.inputs.get('editor').set(s.get('markup'));
		this.inputs.get('enable').set(s.get('enabled'));

		this.render_preview();

		/*
		*  Enable all validators now that a slide is open.
		*  EditorView.hide_slide() disables them.
		*/
		this.validators.enable(true);
		this.update();
	}

	highlight_error(from, to) {
		/*
		*  Highlight lines from-to in the markup editor.
		*/
		return this.editor.session.addMarker(
			new ace_range.Range(from, 0, to, 10),
			'syntax-error-highlight',
			'fullLine'
		);
	}

	clear_error(id) {
		/*
		*  Clear editor highlights.
		*/
		if (id) { this.editor.session.removeMarker(id); }
	}

	render_preview() {
		/*
		*  Render the live markup preview.
		*/
		this.statics.get('label_editor_error').set('');
		this.clear_error(this.error_id);
		this.error_id = null;

		try {
			this.preview.render(this.inputs.get('editor').get());
		} catch (e) {
			if (e instanceof MarkupError) {
				this.statics.get('label_editor_error').set(
					`>> ${e.toString()}`
				);
				this.error_id = this.highlight_error(e.line(), e.line());
			} else {
				throw e;
			}
		}
	}

	set_preview_ratio(r) {
		/*
		*  Set the aspect ratio of the live slide preview.
		*/
		this.preview.set_ratio(r);
		this.update();
	}

	get_editor_input_data() {
		/*
		*  Return the editor input data as an object compatible
		*  with Slide.set().
		*/
		return {
			name:          this.inputs.get('name').get(),
			collaborators: this.inputs.get('collaborators').get(),
			duration:      this.inputs.get('duration').get(),
			index:         this.inputs.get('index').get(),
			animation:     this.inputs.get('animation').get(),
			sched:         this.inputs.get('schedule_enable').get(),
			sched_t_s: util.datetime_to_tstamp(
				this.inputs.get('schedule_date_start').get(),
				this.inputs.get('schedule_time_start').get()
			),
			sched_t_e: util.datetime_to_tstamp(
				this.inputs.get('schedule_date_end').get(),
				this.inputs.get('schedule_time_end').get()
			),
			markup:        this.inputs.get('editor').get(),
			enabled:       this.inputs.get('enable').get()
		}
	}

	is_slide_modified() {
		return !util.object_contains(
			this.get_editor_input_data(),
			this.controller.get_slide().get_data()
		);
	}

	async hide_slide() {
		/*
		*  Hide the currently visible slide.
		*/

		/*
		*  Disable the input validators so that they don't validate
		*  the input as invalid when the field is disabled.
		*/
		this.validators.enable(false);

		if (this.controller.get_state().slide.loaded) {
			await this.controller.close_slide();
		}
		this.inputs.all(function() { this.clear(); });
		this.update();
	}

	async new_slide() {
		/*
		*  Create a new slide. Note that this function
		*  doesn't save the slide automatically.
		*/
		if (!(await this.confirm_slide_hide())) { return; }
		await this.controller.new_slide();
		await this.show_slide(null);
	}

	async save_slide() {
		/*
		*  Save the current slide.
		*/
		let s = this.controller.get_slide();
		s.set(this.get_editor_input_data());

		try {
			await this.controller.save_slide();
			await this.timeline.update(false);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		this.timeline.set_selected(s.get('id'));
		await this.show_slide(null);
	}

	async duplicate_slide() {
		/*
		*  Duplicate the current slide.
		*/
		try {
			await this.controller.duplicate_slide();
			await this.timeline.update(true);
		} catch (e) {
			APIUI.handle_error(e);
		}
	}

	preview_slide() {
		/*
		*  Preview the open slide in a new window.
		*/
		window.open(
			`/app/?preview=${this.controller.get_slide().get('id')}`
		);
	}

	async update_move_slide_options() {
		/*
		*  Update the queue list in the 'Move slide' DropSelect.
		*/
		let queues = null;
		let sq = this.controller.get_slide().get('queue_name');
		try {
			queues = (
				await Queue.get_queues(this.api)
			).filter(q => q !== sq);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		this.move.set_options(queues);
	}

	async move_slide(queue) {
		/*
		*  Move the current slide to 'queue'.
		*/
		await this.controller.move_slide(queue);
		await this.timeline.update(false);
		await this.hide_slide();
		this.update();
	}

	async remove_slide() {
		/*
		*  Remove the current slide.
		*/
		await this.controller.remove_slide();
		await this.timeline.update(false);
		await this.hide_slide();
		this.update();
	}

	update() {
		/*
		*  Update controls state, ie. whether buttons and
		*  inputs are enabled.
		*/
		this.inputs.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.statics.all(
			function (d) { this.state(d); },
			this.controller.get_state()
		);
		this.buttons.all(
			function(d) { this.state(d); },
			this.controller.get_state()
		);
		this.validators.validate();
	}
}
exports.EditorView = EditorView;
