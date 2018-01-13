var _list_old = [];
var _list_current = [];
var _list_ready = false;

var _slides_old = [];
var _slides_current = [];
var _slides_ready = [];

function slides_get() {
	if (_slides_ready) {
		return _slides_current;
	} else {
		return _slides_old;
	}
}

function list_get() {
	if (_list_ready) {
		return _list_current;
	} else {
		return _list_old;
	}
}

function list_retrieve(ready_callback) {
	/*
	*  Retrieve the current slide list asynchronously.
	*  'ready_callback' is called after the list is ready.
	*/
	_list_ready = false;
	_list_old = _list_current.slice();
	_list_current = [];
	api_call(API_ENDP.SLIDE_LIST, null, function(response) {
		_list_current = response;
		_list_ready = true;
		console.log("LibreSignage: Slide list retrieved. (" +
				_list_current.length + " slides)");
		if (ready_callback) {
			ready_callback();
		}
	});
}

function slides_retrieve(ready_callback) {
	/*
	*  Retrieve the slides based on the current
	*  slide list asynchronously. 'ready_callback'
	*  is called after all slides are ready.
	*/
	var list = list_get();
	_slides_ready = false;
	_slides_old = _slides_current.slice();
	_slides_current = [];
	for (i in list) {
		api_call(API_ENDP.SLIDE_GET, {'id': list[i]}, function(response) {
			if (response == null) { return; }
			_slides_current.push(response);

			if (_slides_current.length == list.length) {
				_slides_ready = true;
				console.log("LibreSignage: Slide " +
						"retrieved. (" +
						_slides_current.length +
						" slides)");
				if (ready_callback) {
					ready_callback();
				}
			}
		});
	}
}
