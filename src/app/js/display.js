const DISPLAY_UPDATE_INTERVAL = 2000;
const QUEUE_UPDATE_INTERVAL = 30000;
const DISPLAY = $('#display');

var queue = null;
var c_slide_i = -1;

function display_next_slide(slides, wrap) {
	var n_diff = -1;
	var diff = -1;

	if (!Object.keys(slides).length) { return null; }
	for (var k in slides) {
		n_diff = slides[k].get('index') - c_slide_i;
		if (n_diff > 0 && (n_diff < diff || diff == -1)) {
			diff = n_diff;
		}
	}
	if (diff == -1 && wrap) {
		c_slide_i = -1;
		return display_next_slide(slides, false);
	} else if (diff > 0) {
		c_slide_i += diff;
		for (var k in slides) {
			if (slides[k].get('index') == c_slide_i) {
				if (slides[k].get('enabled')) {
					return slides[k];
				} else {
					return display_next_slide(
						slides,
						false
					);
				}
			}
		}
	}
	return null;
}

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
	var slide = display_next_slide(
		queue.filter({'enabled': true}),
		true
	);

	if (!slide) {
		setTimeout(display_update, DISPLAY_UPDATE_INTERVAL);
		return;
	}

	display_animate(DISPLAY, slide.anim_hide(), () => {
		DISPLAY.html(
			markup_parse(
				sanitize_html(
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
	var params = get_GET_parameters();
	if ('preview' in params) {
		// Preview a slide without starting the display.
		console.log(
			`LibreSignage: Preview slide ` +
			` ${params["preview"]}.`
		);

		var slide = new Slide();
		slide.load(params["preview"], (err) => {
			if (err) {
				console.log(
					"LibreSignage: Failed to " +
					"preview slide!"
				);
				return;
			}
			DISPLAY.html(
				markup_parse(
					sanitize_html(
						slide.get("markup")
					)
				)
			);
		});
	} else if ('q' in params){
		console.log("LibreSignage: Start the display loop.");
		queue = new Queue();
		queue.load(params['q'], () => {
			console.log(
				`LibreSignage: Queue ` +
				`'${params['q']}' loaded. ` +
				`(${queue.length()} slides)`
			);
			setInterval(() => {
				console.log(
					"LibreSignage: Queue update."
				);
				queue.update(() => {
					console.log(
						"LibreSignage: Queue " +
						"update complete."
					);
				});
			}, QUEUE_UPDATE_INTERVAL);
			display_update();
		});
	}
}

$(document).ready(() => {
	var params = get_GET_parameters();
	var noui = false;

	if ('noui' in params && params['noui'] == '1') {
		noui = true;
	} else {
		noui = false;
	}

	api_init(
		{'noui': noui},
		display_setup
	)
});
