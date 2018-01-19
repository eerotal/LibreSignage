var DEFAULT_RENDERER_UPDATE_INTERVAL = 5000;
var SLIDES_RETRIEVE_INTERVAL = 30000;

var display = $('#display');
var c_slide = null;

function _renderer_next_slide(c_slide, wrap) {
	var slides = slides_get();
	if (slides.length) {
		return array_next(
			slides,
			c_slide,
			function(array, key) {
				return array[key]['index'];
			},
			-1, // Start index when !c_slide.
			function() {
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
	} else {
		return null;
	}
}

function renderer_animate(elem, animation, end_callback) {
	/*
	*  Trigger one of the animations defined in 'css/renderer.css'
	*  on 'elem'. If 'end_callback' is not null, it's called when
	*  the animation has finished.
	*/

	elem.addClass(animation);
	elem.one("animationend", function _callback() {
		this.classList.remove(animation);
		if (end_callback) {
			end_callback();
		}
	});
}

function renderer_update() {
	/*
	*  Render the next slide.
	*/
	var slides = slides_get();
	if (!slides.length) { return; }

	renderer_animate(display, 'swipe-left', function() {
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
		display.html(c_slide.markup);

		renderer_animate(display, 'swipe-from-right', null);

		console.log("LibreImage: Changing slide in " +
				c_slide.time + "ms.");

		setTimeout(renderer_update, c_slide.time);
	});
}

setInterval(function() {
	list_retrieve(slides_retrieve);
}, SLIDES_RETRIEVE_INTERVAL);

// Start the renderer 'loop'.
list_retrieve(function() {
	slides_retrieve(function() {
		renderer_update();
	});
});

