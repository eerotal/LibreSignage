var $ = require('jquery');
var DisplayController = require('./displaycontroller.js').DisplayController;
var util = require('ls-util');
var markup = require('ls-markup');
var MarkupSyntaxError = require('ls-markup').err.MarkupSyntaxError;
var dialog = require('ls-dialog');
var APIUI = require('ls-api-ui');

const DISPLAY_UPDATE_INTERVAL = 5000;
const BUFFER_UPDATE_PERIOD = 50;

class DisplayView {
	constructor(api) {
		this.controller = new DisplayController(api);
		this.query_params = util.get_GET_parameters();
		this.slide_buffer = [];
		this.markup_buffer = [];

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
		if ('preview' in this.query_params) {
			await this.preview_slide(this.query_params['preview']);
		} else if ('q' in this.query_params){
			await this.start_render_loop(this.query_params['q']);
		} else {
			await this.prompt_select_queue();
		}
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

	update_buffers() {
		/*
		*  Buffer slide data and markup to improve display performance.
		*/
		let queue = this.controller.get_loaded_queue();

		this.slide_buffer[0] = this.slide_buffer[1];
		this.slide_buffer[1] = queue.get_slides().filter(
			{'enabled': true}
		).next(
			this.slide_buffer[0] != null
				? this.slide_buffer[0].get('index')
				: -1,
			true
		);
		if (this.slide_buffer[1] != null) {
			this.markup_buffer = markup.parse(
				util.sanitize_html(this.slide_buffer[1].get('markup'))
			);
		}
	}

	render() {
		/*
		*  Render the next slide in the loaded slide queue. Slide
		*  data and markup loads are buffered to improve performance.
		*/
		let anim_hide = null;
		if (this.slide_buffer[0] != null) {
			anim_hide = this.slide_buffer[0].anim_hide();
		}

		if (this.slide_buffer[1] == null) {
			this.update_buffers();
			if (this.slide_buffer[1] == null) {
				setTimeout(
					() => this.render(),
					DISPLAY_UPDATE_INTERVAL
				);
				return;
			}
		}

		this.animate($('#display'), anim_hide, () => {
			/*
			* Use vanilla JS for clearing the display because the .html()
			* function seems to leak memory somehow.
			*/
			util.free_multimedia_memory_recursive($('#display')[0]);
			$('#display')[0].innerHTML = '';
			$('#display')[0].innerHTML = this.markup_buffer;

			this.animate(
				$('#display'),
				this.slide_buffer[1].anim_show(),
				() => {
					setTimeout(
						() => this.render(),
						this.slide_buffer[1].get('duration')
					);

					/*
					*  Delay the buffer update for BUFFER_UPDATE_PERIOD
					*  milliseconds. This is done to prevent
					*  animation lag because the animationend event
					*  seems to be fired when the last frame(s?) of
					*  the animation are still running.
					*/
					setTimeout(
						() => this.update_buffers(),
						BUFFER_UPDATE_PERIOD
					);
				}
			);
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
}
exports.DisplayView = DisplayView;
