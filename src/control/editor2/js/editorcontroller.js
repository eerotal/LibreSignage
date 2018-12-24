var APIInterface = require('ls-api').APIInterface;
var APIError = require('ls-api').APIError;
var Slide = require('ls-slide').Slide;
var Queue = require('ls-queue').Queue;
var assert = require('ls-assert').assert;

class EditorController {
	constructor(api) {
		this.api = api;
		this.slide = null;
		this.queue = null;

		this.state = {
			queue: {
				loaded: false
			},
			slide: {
				loaded:      false,
				saved:       false,
				locked:      false,
				owned:       false,
				collaborate: false
			}
		};
	}

	async open_queue(name) {
		this.queue = new Queue(this.api);
		await this.queue.load(name);
		Object.assign(this.state.queue, {
			loaded: true
		});
	}

	async create_queue(name) {
		if (this.queue != null) {
			this.close_queue();
		}

		this.queue = new Queue(this.api);
		await this.queue.create(name);
		Object.assign(this.state.queue, {
			loaded: true
		});
	}

	async remove_queue() {
		assert(this.queue != null, "No queue loaded.");
		await this.queue.remove();
		this.queue = null;
		Object.assign(this.state.queue, {
			loaded: false
		});
	}

	close_queue() {
		this.queue = null;
		Object.assign(this.state.queue, {
			loaded: false
		});
	}

	async close_slide() {
		if (this.slide != null) {
			await this.slide.lock_release();
			this.slide = null;
		}
		Object.assign(this.state.slide, {
			loaded:      false,
			saved:       false,
			locked:      false,
			owned:       false,
			collaborate: false
		});
	}

	async open_slide(id) {
		assert(this.queue != null, "No queue loaded.");
		assert(this.queue.has_slide(id), "No such slide in queue.");

		if (this.slide != null) {
			try {
				await this.slide.lock_release();
			} catch (e) {
				console.warn('EditorController: Lock release failed.');
			}
		}

		this.slide = new Slide(this.api);
		try {
			await this.slide.load(id, true, true);
		} catch (e) {
			switch (e.response.error) {
				case APIError.codes.API_E_NOT_AUTHORIZED:
				case APIError.codes.API_E_LOCK:
					await this.slide.load(id, false, false);
					break;
				default:
					throw e;
			}
		}

		Object.assign(this.state.slide, {
			loaded:      true,
			saved:       true,
			locked:      this.slide.is_locked_from_here(),
			owned:       this.slide.is_owned_by_me(),
			collaborate: this.slide.can_collaborate()
		});
	}

	async new_slide() {
		assert(this.queue != null, "No queue loaded.");

		if (this.slide != null) {
			await this.close_slide();
		}

		this.slide = new Slide(this.api);

		// Set some default values.
		this.slide.set({
			id:            null,
			name:          'NewSlide',
			owner:         this.api.get_session().get_user().get_user(),
			duration:      5000,
			markup:        '',
			index:         0,
			enabled:       true,
			sched:         false,
			sched_t_s:     Math.round(Date.now()/1000),
			sched_t_e:     Math.round(Date.now()/1000),
			animation:     0,
			queue_name:    this.queue.get_name(),
			collaborators: [],
			lock:          null,
			assets:        []
		});

		Object.assign(this.state.slide, {
			loaded:      true,
			saved:       false,
			locked:      true, // Unsaved slides are technically locked.
			owned:       true,
			collaborate: false
		});
	}

	async save_slide() {
		assert(this.slide != null, "No slide to save.");

		await this.slide.save();
		Object.assign(this.state.slide, {
			saved: true
		});
	}

	async move_slide(queue) {
		assert(this.slide != null, "No slide to move.");
		assert(this.state.slide.saved, "Slide not saved.");

		this.slide.set('queue', queue);
		await this.save_slide();
	}

	async duplicate_slide() {
		assert(this.slide != null, "No slide to duplicate.");
		await this.slide.dup();
		await this.queue.update();
	}

	async remove_slide() {
		assert(this.slide != null, "No slide to remove.");

		if (this.slide.get('id') === null) {
			this.slide = null;
		} else {
			await this.slide.remove();
			this.slide = null;
		}

		Object.assign(this.state.slide, {
			loaded:      false,
			saved:       false,
			locked:      false,
			owned:       false,
			collaborate: false
		});
	}

	get_state() { return this.state; }
	get_slide() { return this.slide; }
	get_queue() { return this.queue; }
}
exports.EditorController = EditorController;
