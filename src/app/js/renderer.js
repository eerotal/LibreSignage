const DEFAULT_RENDERER_UPDATE_INTERVAL = 5000;
const SLIDES_RETRIEVE_INTERVAL = 30000;
const DISPLAY = $('#display');

var c_slide = null;

function _renderer_next_slide(c_slide, wrap) {
	var slides = slides_get();
	if (!slides.length) {
		return null;
	}
	return array_next(
		slides,
		c_slide,
		(array, key) => {
			return array[key].get('index');
		},
		-1, // Start index when !c_slide.
		() => {
			/*
			*  If no next exists, attempt to wrap
			*  the 'search'. Note the wrap=false
			*  argument on the _renderer_next_slide()
			*  call below. This makes sure the
			*  array_next calls don't create infinite
			*  recursion if the slides array is empty,
			*  since the next _renderer_next_slide()
			*  call won't attempt to wrap and will
			*  just return null instead.
			*/
			if (!wrap) {
				return null;
			}
			return _renderer_next_slide(null,
						false);
		}
	);
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
	renderer_animate(DISPLAY, 'swipe-left', () => {
		c_slide = _renderer_next_slide(c_slide, true);
		if (!c_slide) {
			/*
			*  Make sure the renderer continues to
			*  update even if no slides currently exist.
			*/
			setTimeout(renderer_update,
				DEFAULT_RENDERER_UPDATE_INTERVAL);
			return;
		}

		DISPLAY.html(markup_parse(sanitize_html(
			c_slide.get('markup')
		)));

		renderer_animate(DISPLAY, 'swipe-from-right', null);

		console.log("LibreImage: Changing slide in " +
				c_slide.get('time') + "ms.");
		setTimeout(renderer_update, c_slide.get('time'));
	});
}

// --- Display setup ---
var params = get_GET_parameters();

if ("preview" in params) {
	// Preview a slide without starting the renderer.
	console.log("LibreSignage: Preview slide " +
			params["preview"] + ".");
	var slide = new Slide();
	slide.load(params["preview"], (err) => {
		if (err) {
			console.log("LibreSignage: Failed to " +
					"preview slide!");
			return;
		}
		DISPLAY.html(markup_parse(sanitize_html(
			slide.get("markup")
		)));
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
