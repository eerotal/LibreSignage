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
	api_call(API_ENDP.SLIDE_LIST, null, (resp) => {
		if (resp.error) {
			throw new Error('API error while fetching ' +
					'slide list.')
		}
		_list_current = resp.slides;
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
	var ready_cnt = list.length;

	_slides_ready = false;
	_slides_old = _slides_current.slice();
	_slides_current = [];

	for (i in list) {
		_slides_current[i] = new Slide();
		_slides_current[i].load(list[i], (status) => {
			if (!status) {
				throw new Error('Error while loading ' +
						'slide!');
				_slides_current[i] = null;
			}

			ready_cnt--;
			if (!ready_cnt) {
				/*
				*  Remove null slides resulting from failed
				*  Slide.load() calls.
				*/
				_slides_current = _slides_current.filter(
					(s) => { return s != null; }
				);

				_slides_ready = true;
				console.log("LibreSignage: Slides " +
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
