var SLIDELIST = $("#slidelist");
var SLIDE_THUMB_SEL_STR = '.slide-thumb';

var _slidelist_current = {};
var _slidelist_old = {};
var _slidelist_ready = true;

const slidelist_btn = (id, index, name, enabled) => `
	<div class="btn slide-cont ${!enabled ? 'slide-cont-dis' : ''}"
		id="slide-btn-${id}"
		onclick="slide_show('${id}')">
		<div class="row m-0 p-0 h-100">
			<div class="col-2 slide-index-cont">
				${index}
			</div>
			<div class="col-10 slide-thumb-cont">
				<iframe class="slide-thumb"
					src="/app?preview=${id}&noui=1"
					frameborder="0">
				</iframe>
			</div>
		</div>
	</div>
`;

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
	*  using the LibreSignage API. 'ready_callback' is
	*  called when the API call has finished.
	*/
	_slidelist_ready = false;
	api_call(API_ENDP.SLIDE_DATA_QUERY,
		{'name': 1, 'index': 1, 'enabled': 1},
		(resp) => {
			if (resp.error) {
				throw new Error("LibreSignage: " +
						"API error.")
			}
			_slidelist_old = {};
			Object.assign(_slidelist_old,
					_slidelist_current);

			_slidelist_current = {};
			Object.assign(_slidelist_current,
					resp.data);

			_slidelist_ready = true;
			console.log("LibreSignage: Slide " +
					"names retrieved.");

			if (ready_callback) {
				ready_callback();
			}
		}
	);
}

function _slidelist_next_id(current_id, list) {
	/*
	*  Get the next slide in 'list' based on the
	*  indices of the slides. If 'current_id' == null,
	*  the first slide is returned. If 'current_id' is
	*  the last slide, null is returned.
	*/
	var min_diff = -1;
	var diff = -1;
	var sel = null;
	var current_index = 0;

	if (current_id == null) {
		current_index = -1;
	} else {
		current_index = list[current_id]['index'];
	}

	for (id in list) {
		diff = list[id]['index'] - current_index;
		if (diff > 0 && min_diff < 0) {
			min_diff = diff;
			sel = id;
		} else if (diff > 0 && diff < min_diff) {
			min_diff = diff;
			sel = id;
		}
	}
	return sel;
}

function _slidelist_update() {
	/*
	*  Update the editor HTML with the new slide list.
	*/
	var id = null;
	var list = slidelist_get();
	console.log("LibreSignage: Update slide list.");

	SLIDELIST.html('');
	while (id = _slidelist_next_id(id, list)) {
		SLIDELIST.append(
			slidelist_btn(
				id,
				list[id]['index'],
				list[id]['name'],
				list[id]['enabled']
			)
		);
	}
	console.log("LibreSignage: Disable logging for "+
			"slide thumbnail iframes (log, warn, error).");
	$(SLIDE_THUMB_SEL_STR).each(function() {
		this.contentWindow.console.log = function() {};
		this.contentWindow.console.warn = function() {};
		this.contentWindow.console.error = function() {};
	});
}

function slidelist_trigger_update() {
	if (_slidelist_ready) {
		console.log("LibreSignage: Trigger slidelist " +
				"update.");
		slidelist_retrieve(_slidelist_update);
	} else {
		console.warn("LibreSignage: Not triggering a " +
				"slidelist update when the " +
				"previous one hasn't completed.");
	}
}
