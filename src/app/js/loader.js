var _list_old = [];
var _list_current = [];
var _list_ready = false;

var _content_old = [];
var _content_current = [];
var _content_ready = [];

function content_get() {
	if (_content_ready) {
		return _content_current;
	} else {
		return _content_old;
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
	*  Retrieve the current content list asynchronously.
	*  'ready_callback' is called after the list is ready.
	*/
	_list_ready = false;
	_list_old = _list_current.slice();
	_list_current = [];
	api_call(API_ENDP.CONTENT_LIST, null, function(response) {
		_list_current = response;
		_list_ready = true;
		console.log("LibreSignage: Content list retrieved. (" +
				_list_current.length + " screens)");
		if (ready_callback) {
			ready_callback();
		}
	});
}

function content_retrieve(ready_callback) {
	/*
	*  Retrieve the content based on the current
	*  content list asynchronously. 'ready_callback'
	*  is called after all content is ready.
	*/
	var list = list_get();
	_content_ready = false;
	_content_old = _content_current.slice();
	_content_current = [];
	for (i in list) {
		api_call(API_ENDP.CONTENT_GET, {'id': list[i]}, function(response) {
			if (response == null) { return; }
			_content_current.push(response);

			if (_content_current.length == list.length) {
				_content_ready = true;
				console.log("LibreSignage: Content " +
						"retrieved. (" +
						_content_current.length +
						" screens)");
				if (ready_callback) {
					ready_callback();
				}
			}
		});
	}
}
