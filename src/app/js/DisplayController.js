var markup = require('ls-markup');
var Slide = require('libresignage/slide/Slide');
var Queue = require('libresignage/queue/Queue');

/**
* Controller class for DisplayView.
*/
class DisplayController {
	/**
	* Construct a new DisplayController.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.QUEUE_UPDATE_INTERVAL = 60000;

		this.api = api;
		this.queue = null;
		this.buffer = null;
		this.queue_iterator = null;
	}

	/**
	* Load a queue and start the queue update loop.
	*
	* @params {string} queue_name The name of the queue to load.
	*/
	async load_queue(queue_name) {
		this.queue = new Queue(this.api);
		await this.queue.load(queue_name);
		this.buffer_slides();
		
		// Initialize the queue iterator.
		this.queue_iterator = this.queue
			.get_slidelist()
			.filter_dict({enabled: true})[Symbol.iterator](true, 1, 0);

		setInterval(async () => {
			try {
				console.log('DisplayController: Queue update.');
				await this.queue.update();
				this.buffer_slides();
			} catch (e) {
				console.warn('DisplayController: Failed to update queue.');
			}
		}, this.QUEUE_UPDATE_INTERVAL);
	}

	/**
	* Buffer the markup of all slides in the loaded queue.
	*/
	buffer_slides() {
		for (let s of this.queue.get_slidelist().filter_dict({enabled: true})) {
			s.transpile();
		}
	}

	/**
	* Get a Slide from the loaded Queue using relative indexing, ie.
	* 1 is the next slide, -1 is the previous one etc.
	*
	* @param {number} step The step to take in the Queue.
	*
	* @returns {Slide} The resulting Slide object.
	*/
	get_slide_rel(step = 1) {
		this.queue_iterator.set_step(step);
		return this.queue_iterator.next().value;
	}

	/**
	* Get the loaded queue.
	*
	* @returns {Queue} The loaded queue.
	*/
	get_queue() { return this.queue; }

	/**
	* Return a sorted list of all queue names.
	*
	* @returns {string[]} The queue names as an array of strings.
	*/
	async get_queues_sorted() {
		return (await Queue.get_queues(this.api)).sort();
	}

	/**
	* Get a Slide object by ID.
	*
	* @params {string} id The ID of the Slide to get.
	*
	* @returns {Slide} The requested Slide object.
	*/
	async get_slide(id) {
		let slide = new Slide(this.api);
		await slide.load(id);
		return slide;
	}
}
module.exports = DisplayController;
