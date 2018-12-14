var $ = require('jquery');
var bootstrap = require('bootstrap');
var UIController = require('ls-uicontrol').UIController;
var UIInput = require('ls-uicontrol').UIInput;
var UIButton = require('ls-uicontrol').UIButton;
var UIStatic = require('ls-uicontrol').UIStatic;
var MultiSelect = require('ls-multiselect').MultiSelect;

var StrValidator = require('ls-validator').StrValidator;
var WhitelistValidator = require('ls-validator').WhitelistValidator;
var BlacklistValidator = require('ls-validator').BlacklistValidator;

var EditorController = require('./editorcontroller.js').EditorController;
var APIUI = require('ls-api-ui');
var User = require('ls-user').User;
var Queue = require('ls-queue').Queue;
var MarkupError = require('ls-markup').err.MarkupError;

var util = require('ls-util');
var ace_range = ace.require('ace/range');

var Timeline = require('./components/timeline.js').Timeline;
var Preview = require('./components/preview.js').Preview;

class EditorView {
	constructor(api) {
		this.api        = api;
		this.controller = new EditorController(api);
		this.ready      = false;

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
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: null,
				defer: () => !this.ready,
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
				defer: () => !this.ready,
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
				defer: () => !this.ready,
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
				defer: () => !this.ready,
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,	
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
				defer: () => !this.ready,
				mod: null,
				getter: e => e.prop('checked'),
				setter: (e, val) => e.prop('checked', val),
				clearer: e => e.prop('checked', false)
			})
		});
		this.statics = new UIController({
			timeline: new UIStatic({
				elem: $('#timeline'),
				cond: () => true,
				enabler: null,
				attach: {
					'component.timeline.click': async (e, data) => {
						await this.show_slide(data.id);
					}
				},
				defer: () => !this.ready,
				getter: null,
				setter: null
			}),
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
				defer: () => !this.ready,
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
				defer: () => !this.ready,
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
				defer: () => !this.ready,
				getter: null,
				setter: null
			}),
			label_editor_error: new UIStatic({
				elem: $('#slide-label-editor-error'),
				cond: () => true,
				enabler: (e, s) => s ? e.show() : e.hide(),
				attach: null,
				defer: () => !this.ready,
				getter: null,
				setter: null
			})
		});
		this.buttons = new UIController({
			new: new UIButton({
				elem: $('#btn-slide-new'),
				cond: d => true,
				enabler: null,
				attach: { click: () => this.new_slide() },
				defer: () => !this.ready
			}),
			save: new UIButton({
				elem: $('#btn-slide-save'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: { click: () => this.save_slide() },
				defer: () => !this.ready
			}),
			duplicate: new UIButton({
				elem: $('#btn-slide-duplicate'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& (d.slide.owned || d.slide.collaborate)
				),
				enabler: null,
				attach: { click: () => this.duplicate_slide() },
				defer: () => !this.ready
			}),
			preview: new UIButton({
				elem: $('#btn-slide-preview'),
				cond: d => d.slide.loaded,
				enabler: null,
				attach: { click: () => this.preview_slide() },
				defer: () => !this.ready
			}),
			move: new UIButton({
				elem: $('#btn-slide-move'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: null,
				attach: { click: () => this.move_slide() },
				defer: () => !this.ready
			}),
			remove: new UIButton({
				elem: $('#btn-slide-remove'),
				cond: d => (
					d.slide.loaded
					&& d.slide.locked
					&& d.slide.owned
				),
				enabler: null,
				attach: null,
				defer: () => !this.ready
			}),
			remove_cancel: new UIButton({
				elem: $('#btn-slide-remove-cancel'),
				cond: d => true,
				enabler: null,
				attach: null,
				defer: () => !this.ready
			}),
			remove_continue: new UIButton({
				elem: $('#btn-slide-remove-continue'),
				cond: d => true,
				enabler: null,
				attach: { click: () => this.remove_slide() },
				defer: () => !this.ready
			})
		});

		this.editor = ace.edit('slide-input');
		this.editor.setTheme('ace/theme/dawn');
		this.editor.blockScrolling = Infinity;

		this.timeline = new Timeline('timeline');

		this.preview = new Preview('preview');
		await this.preview.init();

		this.ready = true;
		await this.show_queue('default');
	}

	async show_queue(name) {
		try {
			await this.controller.open_queue(name);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		this.timeline.show_queue(this.controller.get_queue());
	}

	hide_queue() {
		this.timeline.hide_queue();
		this.controller.close_queue();
	}

	async show_slide(id) {
		let s = null;
		if (id != null) {
			try {
				await this.controller.open_slide(id);
			} catch (e) {
				APIUI.handle_error(e);
				return;
			}
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

		this.update();
	}

	highlight_error(from, to) {
		return this.editor.session.addMarker(
			new ace_range.Range(from, 0, to, 10),
			'syntax-error-highlight',
			'fullLine'
		);
	}

	clear_error(id) {
		if (id) { this.editor.session.removeMarker(id); }
	}

	render_preview() {
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

	async hide_slide() {
		if (this.controller.get_state().slide.loaded) {
			await this.controller.close_slide();
		}
		this.inputs.all(function() { this.clear(); });
	}

	async new_slide() {
		try {
			await this.controller.new_slide();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		this.show_slide(null);
	}

	async save_slide() {
		let s = this.controller.get_slide();
		s.set({
			'name':          this.inputs.get('name').get(),
			'collaborators': this.inputs.get('collaborators').get(),
			'duration':      this.inputs.get('duration').get(),
			'index':         this.inputs.get('index').get(),
			'animation':     this.inputs.get('animation').get(),
			'sched':         this.inputs.get('schedule_enable').get(),
			'sched_t_s': util.datetime_to_tstamp(
				this.inputs.get('schedule_date_start').get(),
				this.inputs.get('schedule_time_start').get()
			),
			'sched_t_e': util.datetime_to_tstamp(
				this.inputs.get('schedule_date_end').get(),
				this.inputs.get('schedule_time_end').get()
			),
			'markup':        this.inputs.get('editor').get(),
			'enabled':       this.inputs.get('enable').get()
		});

		try {
			await this.controller.save_slide();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		// Update slide data in the UI.
		this.show_slide(null);
	}

	async duplicate_slide() {
		try {
			await this.controller.duplicate_slide();
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

	async move_slide() {
		/*
		*  Create the dropdown items for the 'Move slide' dropdown
		*  and attach event handlers to them.
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

		/*
		*  No other queues than the current one.
		*    => Add the '< No queues>' item.
		*/
		if (queues.length === 0) {
			$('#dropdown-slide-move').html(
				$('<button></button>')
					.text('< No queues >')
					.attr({
						class: 'dropdown-item disabled',
						type: 'button'
					})
			);
			return;
		}

		// Add queue buttons to the dropdown.
		$('#dropdown-slide-move').html('');
		for (let q of queues) {
			if (q === this.controller.get_slide().get('queue')) {
				continue;
			}
			$('#dropdown-slide-move').append(
				$('<button></button>')
					.text(q)
					.attr({
						class: 'dropdown-item',
						type: 'button'
					})
					.on('click', async () => {
						try {
							await this.controller.move_slide(q);
						} catch (e) {
							APIUI.handle_error(e);
						}
					})
			);
		}

		try {
			await this.hide_slide();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		this.update();
	}

	async remove_slide() {
		try {
			await this.controller.remove_slide();
			await this.hide_slide();
		} catch (e) {
			APIUI.handle_error(e);
		}
		this.update();
	}

	update() {
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
	}
}
exports.EditorView = EditorView;
