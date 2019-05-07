var Slide = require('ls-slide').Slide;
var Queue = require('ls-queue').Queue;
var util = require('ls-util');
var markup = require('ls-markup');

const QUEUE_UPDATE_INTERVAL = 60000;

class DisplayController {
	constructor(api) {
		this.api = api;
		this.queue = null;

		this.current_slide = null;
		this.current_content = null;

		this.buffered_slide = null;
		this.buffered_content = null;
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

	init_slide_buffers() {
		/*
		*  Try to fill the slide buffers with data. Returns
		*  true if there is enough data available and false
		*  otherwise.
		*/
		if (this.get_current_slide() != null) { return true; }
		for (let i = 0; i < 2 && this.get_current_slide() == null; i++) {
			this.buffer_next_slide();
		}
		return this.get_current_slide() != null;
	}

	buffer_next_slide() {
		/*
		*  Load the next slide in the loaded queue to the
		*  slide buffer.
		*/
		let last = this.buffered_slide;

		// Buffer slide objects.
		this.current_slide = this.buffered_slide;
		this.buffered_slide = this.queue.get_slides().filter(
			{'enabled': true}
		).next(last != null ? last.get('index') : -1, true);

		// Buffer transpiled content.
		this.current_content = this.buffered_content;
		this.buffered_content = markup.parse(
			util.sanitize_html(this.buffered_slide.get('markup'))
		);
	}

	get_loaded_queue() { return this.queue; }
	get_current_slide() { return this.current_slide; }
	get_current_content() { return this.current_content; }
	get_buffered_slide() { return this.buffered_slide; }
	get_buffered_content() { return this.buffered_content; }

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
