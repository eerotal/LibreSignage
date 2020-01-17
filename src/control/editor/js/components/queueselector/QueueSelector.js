var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var StrValidator = require('libresignage/ui/validator/StrValidator');
var BlacklistValidator = require('libresignage/ui/validator/BlacklistValidator');
var Queue = require('libresignage/queue/Queue');
var DropSelect = require('libresignage/ui/components/DropSelect');
var UIController = require('libresignage/ui/controller/UIController')
var UIInput = require('libresignage/ui/controller/UIInput')
var UIButton = require('libresignage/ui/controller/UIButton');
var PromptDialog = require('libresignage/ui/components/Dialog/PromptDialog');
var DropConfirm = require('libresignage/ui/components/DropConfirm');

/**
* QueueSelector component.
*/
class QueueSelector {
	/**
	* Construct a new QueueSelector object.
	*
	* @param {HTMLElement}  container The HTML element where the QueueSelector
	*                                 is created.
	* @param {APIInterface} api       An APIInterface object.
	*/
	constructor(container, api) {
		this.container = container;
		this.api = api;
		this.state = {
			ready: false
		};
	}

	/**
	* Initialize a QueueSelector.
	*/
	async init() {
		this.buttons = new UIController({
			select_open: new UIButton({
				elem: this.container.querySelector('.q-select'),
				cond: () => true,
				enabler: null,
				attach: {
					'component.dropselect.show': async e => {
						await this.update_queue_list();
					},
					'component.dropselect.select': e => {
						this.select_queue(this.select.get_selection());
						this.container.dispatchEvent(
							new Event('component.queueselector.select')
						);
					}
				},
				defer: () => !this.state.ready
			}),
			create: new UIButton({
				elem: this.container.querySelector('.q-create'),
				cond: () => true,
				enabler: null,
				attach: {
					click: async () => {
						this.container.dispatchEvent(
							new Event('component.queueselector.create')
						);
					}
				},
				defer: () => !this.state.ready
			}),
			view: new UIButton({
				elem: this.container.querySelector('.q-view'),
				cond: d => this.select.get_selection() != null,
				enabler: null,
				attach: {
					click: () => {
						this.container.dispatchEvent(
							new Event('component.queueselector.view')
						);
					}
				},
				defer: () => !this.state.ready
			}),
			remove: new UIButton({
				elem: this.container.querySelector('.q-remove'),
				cond: d => this.select.get_selection() != null,
				enabler: (elem, s) => {
					elem.querySelector('.dropconfirm-open').disabled = !s;
				},
				attach: {
					'component.dropconfirm.confirm': () => {
						this.container.dispatchEvent(
							new Event('component.queueselector.remove')
						);
					}
				},
				defer: () => !this.state.ready
			}),
		});

		this.remove = new DropConfirm(
			this.container.querySelector('.q-remove')
		);
		this.remove.set_button_html('<i class="fas fa-trash-alt"></i>');
		this.remove.set_content_html('Remove queue?');

		this.select = new DropSelect(this.container.querySelector('.q-select'));
		this.select.set_button_html('Queue');

		await this.update_queue_list();
		this.update();

		this.state.ready = true;
	}

	/**
	* Update the list of Queues in a QueueSelector.
	*/
	async update_queue_list() {
		let queues = null;
		try {
			queues = await Queue.get_queues(this.api);
		} catch (e) {
			new APIErrorDialog(e);
			return;
		}

		// Preserve the selected queue if it still exists in the list.
		if (queues.includes(this.select.get_selection())) {
			this.select.set_options(queues, this.select.get_selection());
		} else {
			this.select.set_options(queues);
		}
	}

	/**
	* Select a Queue.
	*
	* @param {string} queue The name of the Queue to select.
	*/
	select_queue(queue) {
		this.select.set_button_html(queue);
		this.select.select(queue, false);
		this.update();
	}

	/**
	* Deselect the current Queue.
	*/
	deselect_queue() {
		this.select.set_button_html('Queue');
		this.select.select(null, false);
		this.update();
	}

	/**
	* Update the QueueSelector UI components.
	*/
	update() {
		this.buttons.all(
			function(d) { this.state(d); },
			this.state
		);
	}

	/**
	* Get the name of the currently selected Queue.
	*
	* @return {string|null} A Queue name or null if not Queue is selected.
	*/
	get_selected_queue_name() {
		return this.select.get_selection();
	}
}
module.exports = QueueSelector;
