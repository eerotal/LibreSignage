var CONTENT_RETRIEVE_INTERVAL = 30000;

var display = $('#display');
var c_screen = 0;

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
	*  Render the next screen.
	*/

	var content = content_get();
	if (!content.length) { return; }

	renderer_animate(display, 'swipe-left', function() {
		display.html(content[c_screen].html);
		renderer_animate(display, 'swipe-from-right', null);

		console.log("LibreImage: Changing screen in " +
				content[c_screen].time + "ms.");

		setTimeout(renderer_update, content[c_screen].time);
	});

	if ((c_screen++) == content.length - 1) {
		c_screen = 0;
	}
}

setInterval(function() {
	list_retrieve(content_retrieve);
}, CONTENT_RETRIEVE_INTERVAL);

// Start the renderer 'loop'.
list_retrieve(function() {
	content_retrieve(function() {
		renderer_update();
	});
});
