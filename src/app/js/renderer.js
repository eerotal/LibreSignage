var SLIDES_RETRIEVE_INTERVAL = 30000;

var display = $('#display');
var c_slide = 0;

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
		display.html(slides[c_slide].markup);
		renderer_animate(display, 'swipe-from-right', null);

		console.log("LibreImage: Changing slide in " +
				slides[c_slide].time + "ms.");

		setTimeout(renderer_update, slides[c_slide].time);
	});

	if ((c_slide++) == slides.length - 1) {
		c_slide = 0;
	}
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
