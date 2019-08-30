var markup = require('ls-markup');

var Slide = require('libresignage/slide/Slide');
var Queue = require('libresignage/queue/Queue');

const QUEUE_UPDATE_INTERVAL = 60000;

class DisplayController {
	constructor(api) {
		this.api = api;
		this.queue = null;

		this.current_slide = null;
		this.buffered_slide = null;

		this.slidelist_iterator = null;
	}

	async init_queue_update_loop(queue_name) {
		/*
		*  Initialize the queue update loop.
		*/
		this.queue = new Queue(this.api);
		await this.queue.load(queue_name);

		setInterval(async () => {
			try {
				console.log('DisplayController: Queue update.');
				await this.queue.update();
			} catch (e) {
				console.warn(
					'DisplayController: Failed to update queue.'
				);
			}
		}, QUEUE_UPDATE_INTERVAL);
	}

	init_slide_buffer() {
		if (this.get_buffered_slide() != null) { return true; }
		this.buffer_next_slide();
		return this.get_buffered_slide() != null;
	}

	buffer_next_slide(step = 1) {
		// Initialize the iterator if it's null.
		if (this.slidelist_iterator == null) {
			this.slidelist_iterator = this.queue.get_slides().filter(
				{'enabled': true}
			)[Symbol.iterator](true, step, 0);
		}

		// Buffer the next slide.
		this.slidelist_iterator.set_step(step);
		this.current_slide = this.buffered_slide;
		this.buffered_slide = this.slidelist_iterator.next().value;
		
		if (this.current_slide != null) {
			this.current_slide.transpile_html_buffer();
		}
		if (this.buffered_slide != null) {
			this.buffered_slide.transpile_html_buffer();
		}
	}

	get_loaded_queue() { return this.queue; }
	get_current_slide() { return this.current_slide; }
	get_buffered_slide() { return this.buffered_slide; }

	async get_queues_sorted() {
		/*
		*  Return a sorted list of all queues.
		*/
		let queues = null;
		queues = await Queue.get_queues(this.api);
		queues.sort();
		return queues;
	}

	async get_slide(id) {
		/*
		*  Get the slide with the id 'id'.
		*/
		let slide = new Slide(this.api);
		await slide.load(id);
		return slide;
	}
}
exports.DisplayController = DisplayController;
