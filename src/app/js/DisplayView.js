var markup = require('ls-markup');
var MarkupSyntaxError = require('ls-markup').err.MarkupSyntaxError;

var Util = require('libresignage/util/Util');
var UIController = require('libresignage/ui/controller/UIController')
var UIButton = require('libresignage/ui/controller/UIButton');
var UIStatic = require('libresignage/ui/controller/UIStatic')
var BaseComponent = require('libresignage/ui/components/BaseComponent');
var SelectDialog = require('libresignage/ui/components/Dialog/SelectDialog');
var APIErrorDialog = require('libresignage/ui/components/Dialog/APIErrorDialog');
var DisplayController = require('./DisplayController.js');
var Timeout = require('libresignage/misc/Timeout');

/**
* View class for the display page.
*/
class DisplayView extends BaseComponent {
	/**
	* Construct a new DisplayView object.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		super();

		this.DISPLAY_UPDATE_INTERVAL = 5000;
		this.CONTROLS_VISIBLE_PERIOD = 3000;

		let query = Util.get_GET_parameters();

		this.controller = new DisplayController(api);
		this.queue = null;
		this.slide = null;

		this.statics = null;
		this.buttons = null;

		this.controls_timeout_id = null;
		this.render_timeout = null;

		this.init_state({
			noui: 'noui' in query,
			static: 'static' in query,
			silent: 'silent' in query,
			controls: false,
			preview: false,
			ready: false
		});

		if ('preview' in query) {
			this.slide = query.preview;
		} else if ('queue' in query) {
			this.queue = query.queue;
		}

		// Disable logging if needed.
		if (this.get_state('silent')) {
			console.log = () => {};
			console.warn = () => {};
			console.error = () => {};
		}
	}

	/**
	* Initialize the DisplayView.
	*/
	async init() {
		this.statics = new UIController({
			body: new UIStatic({
				elem: document.querySelector('body'),
				cond: () => true,
				enabler: null,
				attach: {
					mousemove: () => this.show_controls(),
					touchmove: () => this.show_controls(),
					click: () => this.show_controls()
				},
				defer: () => !this.state('ready') || this.state('preview'),
				getter: null,
				setter: null
			})
		});

		this.buttons = new UIController({
			back: new UIButton({
				elem: document.querySelector('#controls .left'),
				cond: d => d.controls,
				enabler: (elem, s) => {
					if (s) {
						// The controls are initially hidden; show them.
						elem.style.visibility = 'visible';
						elem.classList.remove('controls-hidden');
					} else {
						elem.classList.add('controls-hidden');
					}
				},
				attach: { click: e => this.skip_backward() },
				defer: () => !this.state('ready')
			}),
			forward: new UIButton({
				elem: document.querySelector('#controls .right'),
				cond: d => d.controls,
				enabler: (elem, s) => {
					if (s) {
						// The controls are initially hidden; show them.
						elem.style.visibility = 'visible';
						elem.classList.remove('controls-hidden');
					} else {
						elem.classList.add('controls-hidden');
					}
				},
				attach: { click: e => this.skip_forward() },
				defer: () => !this.state('ready')
			})
		});

		// Setup the slide display.
		if (this.slide != null) {
			await this.preview_slide(this.slide);
		} else if (this.queue != null) {
			await this.start_render_loop(this.queue);
		} else {
			await this.prompt_select_queue();
		}

		this.state('ready', true);
	}

	/**
	* Display the LibreSignage startup splash screen.
	*/
	async show_splash() {
		let splash = document.querySelector('#splash');
		let ret = new Promise((resolve, reject) => {
			let listener = () => {
				splash.removeEventListener('animationend', listener)
				splash.classList.remove('splash-fade');
				resolve();
			};

			splash.addEventListener('animationend', listener);
		});
		splash.classList.add('splash-fade');
		return ret;
	}

	/**
	* Make the manual slide forward/backward controls visible.
	*/
	show_controls() {
		if (this.controls_timeout_id != null) {
			clearTimeout(this.controls_timeout_id);
		}

		this.state('controls', true);
		this.apply_state();

		this.controls_timeout_id = setTimeout(() => {
			this.controls_timeout_id = null;
			this.state('controls', false);
			this.apply_state();
		}, this.CONTROLS_VISIBLE_PERIOD);
	}

	/**
	* Start the slide rendering loop.
	*
	* @param {string} queue_name The name of the queue to load.
	*/
	async start_render_loop(queue_name) {
		await this.show_splash();
		try {
			await this.controller.load_queue(queue_name);
		} catch (e) {
			this.APIErrorDialog_wrapper(e);
			return;
		}
		this.render();
	}

	/**
	* Skip forward in the slide queue.
	*/
	skip_forward() {
		// Don't skip when an animation is running.
		if (!this.render_timeout.is_active()) { return; }

		this.render_timeout.exec();
	}

	/**
	* Skip backward in the slide queue.
	*/
	skip_backward() {
		// Don't skip when an animation is running.
		if (!this.render_timeout.is_active()) { return; }

		this.render_timeout.cancel();
		this.render(true);
	}

	/**
	* Trigger an animation on a HTML element.
	*
	* @param {HTMLElement} elem    The HTML element to trigger the
	*                              animation on.
	* @param {string[]}    classes The animation CSS classes to add
	*                              to the element.
	* @param {function}    hook    A hook that's called once the animation
	*                              has finished.
	*/
	animate(elem, classes, hook) {
		if (!classes) {
			if (hook) { hook(); }
			return;
		}

		elem.classList.add(...classes);

		let listener = e => {
			e.target.removeEventListener('animationend', listener);
			e.target.classList.remove(...classes);
			if (hook) { hook(); }
		}
		elem.addEventListener('animationend', listener)
	}

	/**
	* Render the next slide.
	*
	* @param {bool} rev_once If true, the transitions for the rendered slide
	*                        are reversed.
	*/
	render(rev_once) {
		if (this.controller.get_queue().get_slidelist().length == 0) {
			setTimeout(() => this.render(), this.DISPLAY_UPDATE_INTERVAL);
			return;
		}

		let display = document.querySelector('#display');

		let current = this.controller.get_slide_rel(0);
		let next = this.controller.get_slide_rel(rev_once ? -1 : 1)

		// Reverse animations if rev_once == true.
		let anim_1 = !rev_once ? current.anim_hide() : current.anim_show_rev();
		let anim_2 = !rev_once ? next.anim_show() : next.anim_hide_rev();

		this.animate(
			display,
			anim_1,
			() => {
				Util.free_multimedia_memory_recursive(display);
				display.innerHTML = "";
				display.innerHTML = next.get_html();

				this.animate(
					display,
					anim_2,
					() => {
						this.render_timeout = new Timeout(
							() => this.render(),
							next.get('duration')
						);
					}
				);
			}
		);
	}

	/**
	* Preview a slide without starting the display loop.
	*
	* @params {string} id The ID of the slide to display.
	*/
	async preview_slide(id) {
		let s = null;

		this.state('preview', true);
		this.apply_state();

		try {
			s = await this.controller.get_slide(id);
		} catch (e) {
			this.APIErrorDialog_wrapper(e);
			return;
		}

		try {
			let template = document.createElement('template');
			template.innerHTML = markup.parse(s.get('markup'));

			// Don't autoplay video when 'static' is passed in the URL.
			if (this.get_state('static')) {
				for (let e of template.content.querySelectorAll('video')) {
					e.removeAttribute('autoplay')
				}
			}

			document.querySelector('#display').innerHTML = "";
			document.querySelector('#display').appendChild(template.content);

			/*
			* This is a workaround for Chrome (and possibly other browsers?)
			* where the display won't render until a change, eg. a page
			* resize, happens.
			*/
			document.querySelector('#display').style.display = "block";
		} catch (e) {
			if (e instanceof MarkupSyntaxError) {
				console.error(`LibreSignage: ${e.toString}`);
				document.querySelector('#display').innerHTML = "";
			}
			return;
		}
	}

	/**
	* Prompt the user to select a queue to display.
	*/
	async prompt_select_queue() {
		let queues = null;
		let tmp = {};

		try {
			queues = await this.controller.get_queues_sorted();
		} catch (e) {
			this.APIErrorDialog_wrapper(e);
			return;
		}

		/*
		* Convert the queues array into an object with the
		* structure {<queue_name>: <queue_name>} to make
		* it compatible with the SelectDialog class.
		*/
		for (let v in queues) { tmp[queues[v]] = queues[v]; }

		let dialog = new SelectDialog(
			'Select a queue',
			'',
			tmp,
			status => {
				if (!status) { return; }
				window.location.replace(
					`/app/?queue=${tmp[dialog.get_value()]}`
				);
			}
		);
	}

	/**
	* Apply the current state variables to the UIControllers.
	*/
	apply_state() {
		this.statics.all(
			function(d) { this.state(d); },
			this.get_state_vars()
		);
		this.buttons.all(
			function(d) { this.state(d); },
			this.get_state_vars()
		);
	}

	/**
	* Wrapper function for creating APIErrorDialogs.
	*
	* @param {APIError} e The APIError that occured.
	*/
	APIErrorDialog_wrapper(e) {
		if (!this.get_state('noui')) {
			return new APIErrorDialog(e);
		}
	}
}
module.exports = DisplayView;
