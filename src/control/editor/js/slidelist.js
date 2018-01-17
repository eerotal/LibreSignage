var SLIDELIST = $("#slidelist");

var _slidelist_current = {};
var _slidelist_old = {};
var _slidelist_ready = true;

function slidelist_get() {
	if (_slidelist_ready) {
		return _slidelist_current;
	} else {
		return _slidelist_old;
	}
}

function slidelist_retrieve(ready_callback) {
	/*
	*  Retrieve the existing slide names asynchronously
	*  using the LibreSignage API. 'ready_callback' is called
	*  when the API call has finished.
	*/
	_slidelist_ready = false;
	api_call(API_ENDP.SLIDE_NAMES, null, function(response) {
		if (!response || response.error) {
			console.error("LibreSignage: API error!");
			return;
		}
		_slidelist_current = {};
		_slidelist_old = {};
		Object.assign(_slidelist_old, _slidelist_current);
		Object.assign(_slidelist_current, response.names);

		_slidelist_ready = true;
		console.log("LibreSignage: Slide names retrieved!");

		if (ready_callback) {
			ready_callback();
		}
	});
}

function _slidelist_btn_genhtml(id, name) {
	/*
	*  Generate the button HTML for a slide.
	*/
	var html = "";
	html += '<button type="button" class="btn btn-primary ' +
		'btn-slide" ';
	html += 'id="slide-btn-' + id + '"';
	html += 'onclick="slide_show(\'' + id + '\')">';
	html += name + '</button>';
	return html;
}

function _slidelist_update() {
	/*
	*  Update the editor HTML with the new slide names.
	*/
	var html = "";
	var list = slidelist_get();
	console.log("LibreSignage: Updating slide list!");
	for (id in list) {
		html += _slidelist_btn_genhtml(id, list[id]);
	}
	SLIDELIST.html(html);
}

function slidelist_trigger_update() {
	if (_slidelist_ready) {
		console.log("LibreSignage: Trigger slidelist " +
				"update!");
		slidelist_retrieve(_slidelist_update);
	} else {
		console.warn("LibreSignage: Not triggering a " +
				"slidelist update when the " +
				"previous one hasn't completed!");
	}
}
