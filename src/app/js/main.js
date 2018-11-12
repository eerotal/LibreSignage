var $ = require('jquery');
var api = require('ls-api');
var slide = require('ls-slide');
var queue = require('ls-queue');
var slidelist = require('ls-slidelist');
var markup = require('ls-markup');
var util = require('ls-util');
var dialog = require('ls-dialog');

var API = null;
const DISPLAY_UPDATE_INTERVAL = 5000;
const QUEUE_UPDATE_INTERVAL = 60000;
const BUFFER_UPDATE_PERIOD = 50;
const DISPLAY = $('#display');

var c_queue = null;

var slide_buffer = [null, null];
var markup_buffer = null;

function animate(elem, animation, end_callback) {
	/*
	*  Trigger one of the animations defined in 'css/display.css'
	*  on 'elem'. If 'end_callback' is not null, it's called when
	*  the animation has finished.
	*/
	if (!animation) {
		end_callback();
		return;
	}
	elem.addClass(animation);
	elem.one("animationend", (event) => {
		event.target.classList.remove(animation);
		if (end_callback) { end_callback(); }
	});
}

function update_buffers() {
	/*
	*  Buffer slide data and markup to improve display performance.
	*/
	slide_buffer[0] = slide_buffer[1];
	slide_buffer[1] = c_queue.slides.filter(
		{'enabled': true}
	).next(
		slide_buffer[0] != null ? slide_buffer[0].get('index') : -1,
		true
	);
	if (slide_buffer[1] != null) {
		markup_buffer = markup.parse(
			util.sanitize_html(slide_buffer[1].get('markup'))
		);
	}
}

function render() {
	/*
	*  Render the next slide in the loaded slide queue. Slide
	*  data and markup loads are buffered to improve performance.
	*/
	let hide = null;
	if (slide_buffer[0] != null) {
		hide = slide_buffer[0].anim_hide();
	}
	if (slide_buffer[1] == null) {
		update_buffers();
		if (slide_buffer[1] == null) {
			setTimeout(render, DISPLAY_UPDATE_INTERVAL);
			return;
		}
	}
	animate(DISPLAY, hide, () => {
		DISPLAY.html(markup_buffer);
		animate(DISPLAY, slide_buffer[1].anim_show(), () => {
			setTimeout(render, slide_buffer[1].get('duration'));

			/*
			*  Delay the buffer update for BUFFER_UPDATE_PERIOD ms.
			*  This is done to prevent animation lag because the
			*  animationend event seems to be fired when the last
			*  frame(s?) of the animation are still running.
			*/
			setTimeout(update_buffers, BUFFER_UPDATE_PERIOD);
		});
	});
}

function display_setup() {
	let params = util.get_GET_parameters();

	if ('preview' in params) {
		// Preview a slide without starting the display.
		console.log(`LibreSignage: Preview slide ${params['preview']}.`);

		let s = new slide.Slide(API);
		s.load(params['preview'], false, false, (err) => {
			if (API.handle_disp_error(err)) {
				console.log("LibreSignage: Failed to preview slide!");
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

				if ('static' in params) {
					if (content.is('video')) {
						content.removeAttr('autoplay');
					}
					content.find('video').removeAttr('autoplay');
				}

				DISPLAY.html(content);
			} catch (e) {
				if (e instanceof markup.err.MarkupSyntaxError) {
					console.error(`LibreSignage: ${e.message}`);
					DISPLAY.html('');
				}
			}
		});
	} else if ('q' in params){
		console.log("LibreSignage: Start the display loop.");
		c_queue = new queue.Queue(API);
		c_queue.load(params['q'], () => {
			console.log(
				`LibreSignage: Queue '${params['q']}' loaded. ` +
				`(${c_queue.slides.length()} slides)`
			);
			setInterval(() => {
				console.log("LibreSignage: Queue update.");
				c_queue.update(() => {
					console.log("LibreSignage: Queue update complete.");
				});
			}, QUEUE_UPDATE_INTERVAL);
			render();
		});
	} else {
		queue.get_list(API, (qd) => {
			var queues = {};
			qd.sort();
			for (let q of qd) { queues[q] = q; }
			dialog.dialog(
				dialog.TYPE.SELECT,
				'Select a queue',
				'',
				(status, val) => {
					if (!status) { return; }
					window.location.replace(`/app/?q=${val}`);
				},
				queues
			);
		});
	}
}

$(document).ready(() => {
	var params = util.get_GET_parameters();

	// Disable logging if silent=1 is passed in the URL.
	if ('silent' in params) {
		console.log = () => {};
		console.warn = () => {};
		console.error = () => {};
	}

	API = new api.API(
		{'noui': 'noui' in params && params['noui'] == '1'},
		display_setup
	)
});
