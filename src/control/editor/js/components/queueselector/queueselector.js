var $ = require('jquery');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var EventData = require('ls-eventdata').EventData;

var StrValidator = require('libresignage/ui/validator/StrValidator');
var BlacklistValidator = require('libresignage/ui/validator/BlacklistValidator');
var Queue = require('libresignage/queue/Queue');
var DropSelect = require('libresignage/ui/components/DropSelect');
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');
var PromptDialog = require('libresignage/ui/components/Dialog/PromptDialog');
var DropConfirm = require('libresignage/ui/components/DropConfirm');

class QueueSelector {
	constructor(container_id, api) {
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
					'component.dropselect.show': async e => {
						await this.update_queue_list();
					},
					'component.dropselect.select': e => {
						this.select_queue(this.select.get_selection());
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
				enabler: (elem, s) => {
					elem.find('.dropconfirm-open').prop('disabled', !s);
				},
				attach: {
					'component.dropconfirm.confirm': () => {
						this.remove_queue();
					}
				},
				defer: () => !this.state.ready
			}),
		});

		this.remove = new DropConfirm(
			this.container.find('.q-remove')[0]
		);
		this.remove.set_button_html('<i class="fas fa-trash-alt"></i>');
		this.remove.set_content_html('Remove queue?');

		this.select = new DropSelect(
			this.container.find('.q-select')[0]
		);
		this.select.set_button_html('Queue');

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
			new APIErrorDialog(e);
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
			new APIErrorDialog(e);
			return;
		}

		new Promise((resolve, reject) => {
			let dialog = new PromptDialog(
				'Queue name',
				'Please enter a name for the new queue.',
				[new StrValidator({
					min: 1,
					max: null,
					regex: null,
				}, "", true),
				new StrValidator({
					min: null,
					max: this.api.limits.QUEUE_NAME_MAX_LEN,
					regex: null
				}, "The queue name is too long."),
				new StrValidator({
					min: null,
					max: null,
					regex: /^[A-Za-z0-9_-]*$/
				}, "Invalid characters in queue name."),
				new BlacklistValidator({
					bl: queues
				}, "This queue already exists.")],
				status => status ? resolve(dialog.get_value()) : reject(),
			);
		}).then((value) => {
			this.trigger(
				'create',
				new EventData(
					{ queue: value },
					async () => {
						await this.update_queue_list();
						this.select_queue(value);
					},
					null
				)
			);
		}).catch(() => {})
	}

	select_queue(queue) {
		/*
		*  Select a queue by firing the queue select event. The
		*  queue name is passed as 'queue' in the event data object.
		*/
		this.trigger(
			'select',
			new EventData(
				{ queue: queue },
				() => {
					this.state.queue.selected = true;
					this.select.set_button_html(queue);
					this.select.select(queue, false);
					this.update();
				},
				null
			)
		);
	}

	view_queue() {
		/*
		*  Fire the queue view event.
		*/
		this.trigger('view', new EventData());
	}

	remove_queue() {
		/*
		*  Fire the queue remove event.
		*/
		this.trigger(
			'remove',
			new EventData(
				null,
				() => this.deselect_queue(),
				null
			)
		);
	}

	deselect_queue() {
		/*
		*  Deselect the current queue.
		*/
		this.trigger(
			'deselect',
			new EventData(
				null,
				() => {
					this.state.queue.selected = false;
					this.select.set_button_html('Queue');
					this.update();
				},
				null
			)
		);
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
