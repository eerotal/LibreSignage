var $ = require('jquery');
var UIController = require('ls-uicontrol').UIController;
var UIButton = require('ls-uicontrol').UIButton;
var UIInput = require('ls-uicontrol').UIInput;
var Queue = require('ls-queue').Queue;
var APIUI = require('ls-api-ui');
var DropSelect = require('ls-dropselect').DropSelect;

class QueueSelector {
	constructor(container_id, api) {
		this.container = $(`#${container_id}`);
		this.api = api;
		this.state = {
			queue: { selected: false },
			ready: false
		};

		this.buttons = new UIController({
			select_open: new UIButton({
				elem: $(`#${container_id} .q-select`),
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
				elem: $(`#${container_id} .q-create`),
				cond: () => true,
				enabler: null,
				attach: { click: () => this.create_queue() },
				defer: () => !this.state.ready
			}),
			view: new UIButton({
				elem: $(`#${container_id} .q-view`),
				cond: d => d.queue.selected,
				enabler: null,
				attach: { click: () => this.view_queue() },
				defer: () => !this.state.ready
			}),
			remove: new UIButton({
				elem: $(`#${container_id} .q-remove`),
				cond: d => d.queue.selected,
				enabler: null,
				attach: { click: () => this.view_queue() },
				defer: () => !this.state.ready
			})
		});

		this.select = new DropSelect($(`#${container_id} .q-select`)[0]);
		this.select.set_button_text('Queue');

		this.update();

		this.state.ready = true;
	}

	async update_queue_list() {
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
		this.trigger('select', { queue: queue });
		this.state.queue.selected = true;
		this.update();
	}

	deselect_queue() {
		this.trigger('deselect');
		this.state.queue.selected = false;
		this.update();
	}

	create_queue() {
		let name = "test";
		this.trigger('create', { name: name });
	}

	view_queue() {
		this.trigger('view');
	}

	remove_queue() {
		this.trigger('remove');
		this.deselect_queue();
	}

	async update() {
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
