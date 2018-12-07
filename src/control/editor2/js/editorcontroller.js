var APIInterface = require('ls-api').APIInterface;
var APIError = require('ls-api').APIError;
var Slide = require('ls-slide').Slide;
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
			locked:      false,
			owned:       false,
			collaborate: false
		});
	}

	async open_slide(id) {
		if (this.slide != null) {
			try {
				await this.slide.lock_release();
			} catch (e) {}
		}
		this.slide = new Slide(this.api);
		try {
			await this.slide.load(id, true, true);
		} catch (e) {
			switch (e) {
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
			locked:      this.slide.is_locked_from_here(),
			owned:       this.slide.is_owned_by_me(),
			collaborate: this.slide.can_collaborate()
		});
	}

	async save_slide() {
		assert(this.slide != null, "No slide to save.");
		await this.slide.save();
	}

	async move_slide(queue) {
		assert(this.slide != null, "No slide to move.");
		this.slide.set('queue', queue);
		await this.save_slide();
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
