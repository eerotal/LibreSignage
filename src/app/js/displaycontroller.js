var Slide = require('ls-slide').Slide;
var Queue = require('ls-queue').Queue;

const QUEUE_UPDATE_INTERVAL = 60000;

class DisplayController {
	constructor(api) {
		this.api = api;
		this.queue = null;
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

	get_loaded_queue() {
		/*
		*  Get the queue loaded by init_queue_update_loop().
		*/
		return this.queue;
	}

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
