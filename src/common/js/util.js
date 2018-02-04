function resource_get(url, ready_callback) {
	var resource_req = new XMLHttpRequest();
	resource_req.addEventListener('load', ready_callback);
	resource_req.open('GET', url);
	resource_req.send();
}

function array_next(arr, current, val_getter, start_val, no_next) {
	/*
	*  Get the next value (or object) in an array (or object).
	*  Arguments:
	*    - 'arr' is the array with the values.
	*    - 'current' is the current value.
	*    - 'val_getter' is a function for getting values from
	*      more complex array structures such as object arrays.
	*      This argument can for example be used to get the next
	*      object in an object array based on a value the objects
	*      have. This argument should be a function of the form
	*      function(array, key) { return <The comparison value>; }
	*      or null for normal arrays.
	*    - 'start_val' is the starting value to use if 'current'
	*      is not defined. This is especially handy when 'arr' is
	*      an array of objects where it's hard to define a fake
	*      initial 'current' argument. For normal integers 'current'
	*      can just be set to the start value.
	*    - 'no_next' is a callback function that's called if the next
	*      value doesn't exist. The return value of 'no_next' is then
	*      returned by this function. This can be set to null if it
	*      isn't needed.
	*
	*    Note that setting both 'current' and 'start_val' to null
	*    will result in this function throwing an error. One of them
	*    can, however, be set to null.
	*
	*  Return value:
	*    This function returns the next value (or object) or null if
	*    the next value doesn't exist. If 'no_next' is defined, its
	*    return value is returned instead of null if the next value
	*    doesn't exist.
	*/
	var c_val = 0;
	var min_diff = -1;
	var diff = -1;
	var sel = null;
	var val = 0;
	var arr_keys = Object.keys(arr);

	if (!val_getter) {
		val_getter = function(arr, key) {
			return arr[index];
		};
	}

	if (current == null) {
		if (start_val != null) {
			c_val = start_val;
		} else {
			throw new Error("LibreSignage: array_next_val(): " +
					"Invalid undefined " +
					"current value!");
		}
	} else {
		c_val = val_getter([current], 0);
	}

	for (var k in arr_keys) {
		val = val_getter(arr, arr_keys[k]);
		diff = val - c_val;
		if ((diff > 0 && min_diff < 0) ||
			(diff > 0 && diff < min_diff)) {
			min_diff = diff;
			sel = arr[arr_keys[k]];
		}
	}

	if (sel == null && no_next) {
		return no_next();
	}
	return sel;

}

function get_GET_parameters() {
	/*
	*  Get the HTTP GET parameters in an associative array.
	*/
	var query_str = window.location.search.substr(1);
	var params_strs = [];
	var params = [];
	var tmp = [];

	if (!query_str.length) {
		return [];
	} else {
		params_strs = query_str.split('&');
	}

	for (var i in params_strs) {
		tmp = params_strs[i].split('=');
		params[decodeURIComponent(tmp[0])] =
			decodeURIComponent(tmp[1]);
	}
	return params;
}

function sanitize_html(src) {
	// Sanitize HTML tags.
	return $("<div></div>").text(src).html();
}
