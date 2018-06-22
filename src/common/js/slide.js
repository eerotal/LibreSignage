/*
*  LibreSignage JS Slide object definition with functions
*  for loading data through the LibreSignage API.
*/

function Slide() {
	// Map animation identifiers to CSS classes.
	this.ANIM_MAP = {
		0: {
			hide: null,
			show: null,
		},
		1: {
			hide: 'swipe-left',
			show: 'swipe-from-right'
		},
		2: {
			hide: 'swipe-right',
			show: 'swipe-from-left'
		},
		3: {
			hide: 'swipe-up',
			show: 'swipe-from-below'
		},
		4: {
			hide: 'swipe-down',
			show: 'swipe-from-above'
		}
	};
	this.data = {};

	this.load = function(id, callback) {
		/*
		*  Load the slide with ID 'id' using the LibreSignage
		*  API. The function 'callback' is called after the
		*  API call has completed. The received API error code
		*  is passed to the callback function as the first
		*  argument.
		*/
		api_call(API_ENDP.SLIDE_GET, { 'id': id }, (resp) => {
			var keys = [];
			if (resp.error) {
				throw new Error("LibreSignage API error!");
				if (callback) {
					callback(resp.error);
				}
				return;
			}
			Object.assign(this.data, resp['slide']);
			if (callback) {
				callback(resp.error);
			}
		});
	}

	this.save = function(callback) {
		/*
		*  Save this slide using the LibreSignage API. The
		*  function 'callback' is called after the API call
		*  has completed. The received API error code is
		*  passed to the callback function as the first
		*  argument.
		*/
		api_call(API_ENDP.SLIDE_SAVE, this.data, (resp) => {
			if (resp.error) {
				console.error(
					"LibreSignage: API error!"
				);
				if (callback) {
					callback(resp.error);
				}
				return;
			}
			this.set(resp);
			if (callback) {
				callback(resp.error);
			}
		});
	}

	this.remove = function(id, callback) {
		/*
		*  If 'id' is defined, remove the slide with the
		*  ID 'id' using the LibreSignage API. Otherwise
		*  remove the currently loaded slide. The function
		*  'callback' is called after the API call has
		*  completed. An API error code is passed to the
		*  callback function as the first argument.
		*/

		var r_id = "";
		if (id) {
			r_id = id;
		} else if (this.data.id) {
			r_id = this.data.id;
		} else {
			throw new Error("No slide ID specified " +
					"for removal.");
		}

		api_call(API_ENDP.SLIDE_RM, { 'id': r_id }, (resp) => {
			if (resp.error) {
				console.error("LibreSignage: API error.");
				if (callback) {
					callback(resp.error);
				}
				return;
			}
			if (callback) {
				callback(resp.error);
			}
		});
	}

	this.set = function(data) {
		/*
		*  Copy 'data' to this slide's data array. Existing
		*  data is overwritten.
		*/
		Object.assign(this.data, data);
	}

	this.clear = function() { this.data = {}; }
	this.get = function(key) { return this.data[key]; }

	this.anim_hide = function() {
		return this.ANIM_MAP[this.get('animation')].hide;
	}

	this.anim_show = function() {
		return this.ANIM_MAP[this.get('animation')].show;
	}
}
