var $ = require('jquery');
var DisplayController = require('./displaycontroller.js').DisplayController;
var util = require('ls-util');
var markup = require('ls-markup');
var MarkupSyntaxError = require('ls-markup').err.MarkupSyntaxError;
var dialog = require('ls-dialog');
var APIUI = require('ls-api-ui');
var UIController = require('ls-uicontrol').UIController;
var UIButton = require('ls-uicontrol').UIButton;
var UIStatic = require('ls-uicontrol').UIStatic;
var BaseView = require('ls-baseview').BaseView;

const DISPLAY_UPDATE_INTERVAL = 5000;
const BUFFER_UPDATE_PERIOD = 50;
const CONTROLS_VISIBLE_PERIOD = 3000;

class DisplayView extends BaseView {
	constructor(api) {
		super();

		this.controller = new DisplayController(api);
		this.query_params = util.get_GET_parameters();
		this.slide_buffer = [];
		this.markup_buffer = [];

		this.statics = null;
		this.buttons = null;

		this.controls_interval_id = null;

		this.ui_state = {
			controls: false
		};

		// Disable logging if silent=1 was passed in the URL.
		if ('silent' in this.query_params) {
			console.log = () => {};
			console.warn = () => {};
			console.error = () => {};
		}

		// Report errors in console if noui=1 was passed in the URL.
		if ('noui' in this.query_params) {
			APIUI.handle_error = (e) => {
				console.error(e.message);
			};
		}
	}

	async init() {
		/*
		*  Initialize the DisplayView.
		*/
		this.statics = new UIController({
			body: new UIStatic({
				elem: $('body'),
				cond: () => true,
				enabler: null,
				attach: {
					mousemove: () => this.show_controls(),
					touchmove: () => this.show_controls(),
					click: () => this.show_controls()
				},
				defer: () => !this.state('ready'),
				getter: null,
				setter: null
			})
		});
		this.buttons = new UIController({
			back: new UIButton({
				elem: $('#controls .left'),
				cond: d => d.controls,
				enabler: (elem, s) => {
					if (s) {
						// The controls are initially hidden; show them.
						elem.css('visibility', 'visible');

						elem.removeClass('controls-hidden');
					} else {
						elem.addClass('controls-hidden');
					}
				},
				attach: { click: e => this.skip_backward() },
				defer: () => !this.state('ready')
			}),
			forward: new UIButton({
				elem: $('#controls .right'),
				cond: d => d.controls,
				enabler: (elem, s) => {
					if (s) {
						// The controls are initially hidden; show them.
						elem.css('visibility', 'visible');

						elem.removeClass('controls-hidden');
					} else {
						elem.addClass('controls-hidden');
					}
				},
				attach: { click: e => this.skip_forward() },
				defer: () => !this.state('ready')
			})
		});

		if ('preview' in this.query_params) {
			await this.preview_slide(this.query_params['preview']);
		} else if ('q' in this.query_params){
			await this.start_render_loop(this.query_params['q']);
		} else {
			await this.prompt_select_queue();
		}

		this.state('ready', true);
	}

	async show_splash() {
		/*
		*  Display the LibreSignage splash screen.
		*/
		let splash = $('#splash');
		let ret = new Promise((resolve, reject) => {
			splash.one('animationend', () => {
				splash.removeClass('splash-fade');
				resolve();
			});
		});
		splash.addClass('splash-fade');
		return ret;
	}

	show_controls() {
		if (this.controls_interval_id != null) {
			clearInterval(this.controls_interval_id);
		}

		this.ui_state.controls = true;
		this.apply_state();

		this.controls_interval_id = setInterval(() => {
			this.controls_interval_id = null;
			this.ui_state.controls = false;
			this.apply_state();
		}, CONTROLS_VISIBLE_PERIOD);
	}

	skip_forward() {
		console.log('Skip forward.');
	}

	skip_backward() {
		console.log('Skip backward.');
	}

	async start_render_loop(queue_name) {
		/*
		*  Display the LibreSignage splash and start the
		*  main display render loop.
		*/
		await this.show_splash();
		try {
			await this.controller.init_queue_update_loop(queue_name);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}
		this.render();
	}

	animate(elem, animation, end_hook) {
		/*
		*  Trigger one of the animations defined in 'css/display.css'
		*  on 'elem'. If 'end_hook' is not null, it's called when
		*  the animation has finished.
		*/
		if (!animation) {
			end_hook();
			return;
		}
		elem.addClass(animation);
		elem.one('animationend', (event) => {
			event.target.classList.remove(animation);
			if (end_hook) { end_hook(); }
		});
	}

	render() {
		/*
		*  Render the next slide in the loaded slide queue. Slide
		*  data and markup loads are buffered to improve performance.
		*/
		let slide = null;

		// Wait for slides to appear in empty queues.
		if (!this.controller.init_slide_buffers()) {
			setTimeout(() => this.render(), DISPLAY_UPDATE_INTERVAL);
			return;
		}

		slide = this.controller.get_current_slide();
		$('#display').html(this.controller.get_current_content());

		this.animate($('#display'), slide.anim_show(), () => {
			setTimeout(() => {
				setTimeout(() => {
					this.animate($('#display'), slide.anim_hide(), () => {
						this.render();
					});
				}, slide.get('duration') - BUFFER_UPDATE_PERIOD);

				this.controller.buffer_next_slide();
			}, BUFFER_UPDATE_PERIOD);
		});
	}

	async preview_slide(id) {
		/*
		*  Preview a slide without starting the display loop.
		*/
		let s = null;
		try {
			s = await this.controller.get_slide(id);
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		try {
			let content = $(
				markup.parse(
					util.sanitize_html(
						s.get('markup')
					)
				)
			);
			if ('static' in this.query_params) {
				// Don't autoplay video when 'static' is passed in URL.
				if (content.is('video')) {
					content.removeAttr('autoplay');
				}
				content.find('video').removeAttr('autoplay');
			}
			$('#display').html(content);

			/*
			*  This $('#display').show() call is a workaround for Chrome
			*  (and possibly other browsers?) where the display won't
			*  render until a change, eg. a page resize, happens.
			*/
			$('#display').show();
		} catch (e) {
			if (e instanceof MarkupSyntaxError) {
				console.error(`LibreSignage: ${e.toString}`);
				$('#display').html('');
			}
			return;
		}
	}

	async prompt_select_queue() {
		/*
		*  Prompt the user to select a queue to display.
		*/
		let queues = null;
		let tmp = {};

		try {
			queues = await this.controller.get_queues_sorted();
		} catch (e) {
			APIUI.handle_error(e);
			return;
		}

		/*
		*  Convert the queues array into an object with the
		*  structure {<queue_name>: <queue_name>} to make
		*  it compatible with the Dialog class.
		*/
		for (let v in queues) { tmp[queues[v]] = queues[v]; }

		dialog.dialog(
			dialog.TYPE.SELECT,
			'Select a queue',
			'',
			(status, val) => {
				if (!status) { return; }
				window.location.replace(`/app/?q=${val}`);
			},
			tmp
		);
	}

	apply_state() {
		this.statics.all(
			function(d) { this.state(d); },
			this.ui_state
		);
		this.buttons.all(
			function(d) { this.state(d); },
			this.ui_state
		);
	}
}
exports.DisplayView = DisplayView;
