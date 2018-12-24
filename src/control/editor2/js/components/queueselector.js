var $ = require('jquery');
var UIController = require('ls-uicontrol').UIController;
var UIButton = require('ls-uicontrol').UIButton;
var UIInput = require('ls-uicontrol').UIInput;
var Queue = require('ls-queue').Queue;
var APIUI = require('ls-api-ui');
var DropSelect = require('ls-dropselect').DropSelect;
var dialog = require('ls-dialog');

var StrValidator = require('ls-validator').StrValidator;
var BlacklistValidator = require('ls-validator').BlacklistValidator;

class QueueSelector {
	constructor (container_id, api) {
		this.container = $(`#${container_id}`);
		this.api = api;
		this.state = {
			queue: { selected: false },
			ready: false
		};
	}

	async init() {
		this.buttons = new UIController({
			select_open: new UIButton({
				elem: this.container.find('.q-select'),
				cond: () => true,
				enabler: null,
				attach: {
					'component.dropselect.show': async () => {
						await this.update_queue_list();
					},
					'component.dropselect.select': (e, data) => {
						this.select_queue(data.option);
					}
				},
				defer: () => !this.state.ready
			}),
			create: new UIButton({
				elem: this.container.find('.q-create'),
				cond: () => true,
				enabler: null,
				attach: { click: async () => await this.create_queue() },
				defer: () => !this.state.ready
			}),
			view: new UIButton({
				elem: this.container.find('.q-view'),
				cond: d => d.queue.selected,
				enabler: null,
				attach: { click: () => this.view_queue() },
				defer: () => !this.state.ready
			}),
			remove: new UIButton({
				elem: this.container.find('.q-remove'),
				cond: d => d.queue.selected,
				enabler: null,
				attach: { click: () => this.remove_queue() },
				defer: () => !this.state.ready
			})
		});

		this.select = new DropSelect(this.container.find('.q-select')[0]);
		this.select.set_button_text('Queue');

		await this.update_queue_list();
		this.update();

		this.state.ready = true;
	}

	async update_queue_list() {
		/*
		*  Update the queue selector list.
		*/
		let queues = null;
		try {
			queues = await Queue.get_queues(this.api);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		// Preserve the selected queue if it still exists in the list.
		if (queues.includes(this.select.get_selection())) {
			this.select.set_options(
				queues,
				this.select.get_selection()
			);
		} else {
			this.select.set_options(queues);
		}
	}

	select_queue(queue) {
		/*
		*  Select a queue by firing the queue select event. The
		*  queue name is passed as 'queue' in the event data object.
		*/
		this.state.queue.selected = true;
		this.select.set_button_text(queue);
		this.select.select(queue, false);
		this.update();

		this.trigger('select', { queue: queue });
	}

	deselect_queue() {
		/*
		*  Fire the queue deselect event.
		*/
		this.state.queue.selected = false;
		this.select.set_button_text('Queue');
		this.update();

		this.trigger('deselect');
	}

	async create_queue() {
		/*
		*  Create a new queue. This function prompts for the
		*  new queue name and fires the queue create event.
		*  The queue name is passed as 'queue' in the event
		*  data object.
		*/
		let queues = null;
		try {
			queues = await Queue.get_queues(this.api);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		new Promise((resolve, reject) => {
			dialog.dialog(
				dialog.TYPE.PROMPT,
				'Queue name',
				'Please enter a name for the new queue.',
				(action, val) => action ? resolve(val) : reject(),
				[new StrValidator({
					min: null,
					max: null,
					regex: /^[A-Za-z0-9_-]*$/
				}, "Invalid characters in queue name."),
				new StrValidator({
					min: 1,
					max: null,
					regex: null,
				}, "The queue name is too short."),
				new StrValidator({
					min: null,
					max: this.api.limits.QUEUE_NAME_MAX_LEN,
					regex: null
				}, "The queue name is too long."),
				new BlacklistValidator({
					bl: queues
				}, "This queue already exists.")]
			)
		}).then((value) => {
			this.trigger('create', { queue: value });
		}).catch(() => {})
	}

	view_queue() {
		/*
		*  Fire the queue view event.
		*/
		this.trigger('view');
	}

	remove_queue() {
		/*
		*  Fire the queue remove event.
		*/
		this.deselect_queue();
		this.trigger('remove');
	}

	update() {
		this.buttons.all(
			function(d) { this.state(d); },
			this.state
		);
	}

	trigger(event, data) {
		this.container.trigger(
			`component.queueselector.${event}`,
			data
		);
	}
}
exports.QueueSelector = QueueSelector;
