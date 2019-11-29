var Queue = require('libresignage/queue/Queue');
var User = require('libresignage/user/User');
var APIInterface = require('libresignage/api/APIInterface');
var APIError = require('libresignage/api/APIError');
var Assert = require('assert');
var Slide = require('libresignage/slide/Slide');

/**
* Controller class for EditorView.
*/
class EditorController {
	/**
	* Construct a new EditorController instance.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
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
			},
			quota: {
				slides: false
			},
			user: {
				admin: false
			}
		};

		window.addEventListener('unload', () => {
			/*
			* Release slide locks on exit.
			*
			* 'await' is not used here on purpose because
			* the browser won't wait for the API call to
			* finish anyway. That doesn't really matter since
			* as long as the call is sent to the server, any
			* locks are released regardless of whether the
			* response is received or not.
			*/
			console.log('EditorController: Cleanup');
			this.close_slide();
		});
	}

	/**
	* Initialize the EditorController.
	*/
	async init() {
		await this.update_quotas();

		this.state.user = {
			'admin': this.api.get_session().get_user().is_in_group('admin')
		};
	}

	/**
	* Fetch up-to-date userdata and quota state booleans.
	*/
	async update_quotas() {
		let user = new User(this.api);
		await user.load(null);

		Object.assign(this.state.quota, {
			slides: user.get_quota().has_quota('slides')
		})
	}

	/**
	* Open a queue.
	*
	* @param {string} name The name of the queue.
	*/
	async open_queue(name) {
		this.queue = new Queue(this.api);
		await this.queue.load(name);
		Object.assign(this.state.queue, {
			loaded: true
		});
	}

	/**
	* Fetch up-to-date data for the current queue.
	*/
	async update_queue() {
		Assert.ok(this.queue != null, "No queue loaded.");
		await this.queue.update();
	}

	/**
	* Create a new queue.
	*
	* @param {string} name The name of the new queue.
	*/
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

	/**
	* Remove the current queue.
	*/
	async remove_queue() {
		Assert.ok(this.queue != null, "No queue loaded.");
		await this.queue.remove();
		this.queue = null;
		Object.assign(this.state.queue, {
			loaded: false
		});
	}

	/**
	* Close the current queue.
	*/
	close_queue() {
		this.queue = null;
		Object.assign(this.state.queue, {
			loaded: false
		});
	}

	/**
	* Close the current slide.
	*/
	async close_slide() {
		if (this.slide != null) {
			if (this.slide.is_locked_from_here()) {
				await this.slide.lock_release();
			}
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

	/**
	* Open a slide.
	*
	* @param {string} id The ID of the slide to open.
	*/
	async open_slide(id) {
		Assert.ok(this.queue != null, "No queue loaded.");
		Assert.ok(this.queue.has_slide(id), "No such slide in queue.");

		if (this.slide != null && this.slide.is_locked_from_here()) {
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
			if (!(e instanceof APIError)) { throw e; }
			switch (e.get_status()) {
				case HTTPStatus.UNAUTHORIZED:
				case HTTPStatus.LOCKED:
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

	/**
	* Create a new slide.
	*
	* This function doesn't save the slide!
	*/
	async new_slide() {
		Assert.ok(this.queue != null, "No queue loaded.");

		if (this.slide != null && this.slide.has('id')) {
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

	/**
	* Save the current slide.
	*/
	async save_slide() {
		Assert.ok(this.slide != null, "No slide to save.");

		await this.slide.save();
		await this.update_queue();
		await this.update_quotas();

		Object.assign(this.state.slide, {
			saved: true
		});
	}

	/**
	* Move the current slide into a different queue.
	*
	* @param {string} queue The name of the queue where the slide is moved.
	*/
	async move_slide(queue) {
		Assert.ok(this.slide != null, "No slide to move.");
		Assert.ok(this.state.slide.saved, "Slide not saved.");

		this.slide.set({ 'queue_name': queue });
		await this.save_slide();
		await this.update_queue();
	}

	/**
	* Duplicate the current slide.
	*/
	async duplicate_slide() {
		Assert.ok(this.slide != null, "No slide to duplicate.");
		await this.slide.dup();
		await this.update_queue();
	}

	/**
	* Remove the current slide.
	*/
	async remove_slide() {
		Assert.ok(this.slide != null, "No slide to remove.");

		if (this.slide.get('id') === null) {
			this.slide = null;
		} else {
			await this.slide.remove();
			this.slide = null;
			await this.update_queue();
		}

		await this.update_quotas();

		Object.assign(this.state.slide, {
			loaded:      false,
			saved:       false,
			locked:      false,
			owned:       false,
			collaborate: false
		});
	}

	/**
	* Get the state object.
	*
	* @returns {object} All state variables in an object.
	*/
	get_state() { return this.state; }

	/**
	* Get the current slide.
	*
	* @returns {Slide} The current slide object.
	*/
	get_slide() { return this.slide; }

	/**
	* Get the current queue.
	*
	* @returns {Queue} The current Queue object.
	*/
	get_queue() { return this.queue; }
}
module.exports = EditorController;
