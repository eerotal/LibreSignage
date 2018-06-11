const DEFAULT_RENDERER_UPDATE_INTERVAL = 5000;
const SLIDES_RETRIEVE_INTERVAL = 30000;
const DISPLAY = $('#display');

var c_slide_i = 0;

function renderer_next_slide(slides, wrap) {
	var n_diff = -1;
	var diff = -1;

	if (!slides.length) { return null; }

	for (let slide of slides) {
		n_diff = slide.get('index') - c_slide_i;
		if (n_diff > 0 && (n_diff < diff || diff == -1)) {
			diff = n_diff;
		}
	}
	if (diff == -1 && wrap) {
		c_slide_i = -1;
		return renderer_next_slide(slides, false);
	} else if (diff > 0) {
		c_slide_i += diff;
		for (let slide of slides) {
			if (slide.get('index') == c_slide_i) {
				if (slide.get('enabled')) {
					return slide;
				} else {
					return renderer_next_slide(
						slides,
						false
					);
				}
			}
		}
	}
	return null;
}

function renderer_animate(elem, animation, end_callback) {
	/*
	*  Trigger one of the animations defined in 'css/renderer.css'
	*  on 'elem'. If 'end_callback' is not null, it's called when
	*  the animation has finished.
	*/
	elem.addClass(animation);
	elem.one("animationend", (event) => {
		event.target.classList.remove(animation);
		if (end_callback) {
			end_callback();
		}
	});
}

function renderer_update() {
	/*
	*  Render the next slide.
	*/
	var slide = null;
	var slides = slides_get();

	slide = renderer_next_slide(slides, true);
	if (!slide) {
		/*
		*  Set the next update interval if no next
		*  slide exists.
		*/
		setTimeout(
			renderer_update,
			DEFAULT_RENDERER_UPDATE_INTERVAL
		);
		return;
	}

	renderer_animate(DISPLAY, 'swipe-left', () => {
		DISPLAY.html(
			markup_parse(
				sanitize_html(
					slide.get('markup')
				)
			)
		);
		renderer_animate(DISPLAY, 'swipe-from-right', null);
		console.log(
			"LibreSignage: Changing slide in " +
			slide.get('time') + "ms."
		);
		setTimeout(renderer_update, slide.get('time'));
	});
}

function display_setup() {
	var params = get_GET_parameters();
	if ("preview" in params) {
		// Preview a slide without starting the renderer.
		console.log(
			"LibreSignage: Preview slide " +
			params["preview"] + "."
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
	} else {
		// Start the normal renderer 'loop'.
		console.log("LibreSignage: Start the renderer loop.");
		setInterval(() => {
			list_retrieve(slides_retrieve);
		}, SLIDES_RETRIEVE_INTERVAL);
			list_retrieve(() => {
			slides_retrieve(() => {
				renderer_update();
			});
		});
	}
}

$(document).ready(() => {
	api_init(
		null,	// Use default config.
		display_setup
	)
});
