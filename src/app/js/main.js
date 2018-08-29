var $ = require('jquery');
var api = require('ls-api');
var slide = require('ls-slide');
var queue = require('ls-queue');
var slidelist = require('ls-slidelist');
var markup = require('ls-markup');
var util = require('ls-util');
var dialog = require('ls-dialog');

var API = null;
const DISPLAY_UPDATE_INTERVAL = 2000;
const QUEUE_UPDATE_INTERVAL = 30000;
const DISPLAY = $('#display');

var c_queue = null;
var c_slide_i = -1;

function display_animate(elem, animation, end_callback) {
	/*
	*  Trigger one of the animations defined in 'css/display.css'
	*  on 'elem'. If 'end_callback' is not null, it's called when
	*  the animation has finished.
	*/
	if (!animation) { end_callback(); }
	elem.addClass(animation);
	elem.one("animationend", (event) => {
		event.target.classList.remove(animation);
		if (end_callback) {
			end_callback();
		}
	});
}

function display_update() {
	/*
	*  Render the next slide.
	*/
	var slide = c_queue.slides.filter(
		{'enabled': true}
	).next(c_slide_i, true);

	if (slide) {
		c_slide_i = slide.get('index');
	} else {
		c_slide_i = -1;
		setTimeout(display_update, DISPLAY_UPDATE_INTERVAL);
		return;
	}

	display_animate(DISPLAY, slide.anim_hide(), () => {
		DISPLAY.html(
			markup.parse(
				util.sanitize_html(
					slide.get('markup')
				)
			)
		);
		display_animate(DISPLAY, slide.anim_show(), () => {
			setTimeout(display_update, slide.get('time'));
		});
	});
}

function display_setup() {
	var params = util.get_GET_parameters();

	if ('preview' in params) {
		// Preview a slide without starting the display.
		console.log(`LibreSignage: Preview slide ${params['preview']}.`);

		var s = new slide.Slide(API);
		s.load(params['preview'], (err) => {
			if (API.handle_disp_error(err)) {
				console.log("LibreSignage: Failed to preview slide!");
				return;
			}
			try {
				DISPLAY.html(
					markup.parse(
						util.sanitize_html(
							s.get('markup')
						)
					)
				);
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
			display_update();
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
