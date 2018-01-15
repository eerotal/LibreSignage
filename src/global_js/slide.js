/*
*  LibreSignage JS Slide object definition with functions
*  for loading data through the LibreSignage API.
*/

var SLIDE_REQ_KEYS = [
	'id',
	'time',
	'index',
	'markup'
];

function Slide() {
	this.data = {};
	this.REQ_KEYS = SLIDE_REQ_KEYS;

	this._verify = function(data) {
		/*
		*  Verify the data of this slide.
		*/

		for (k in data) {
			if (!this.REQ_KEYS.includes(k)) {
				return false;
			}
		}
		return true;
	}

	this.load = function(id, callback) {
		/*
		*  Load the slide with ID 'id' using the LibreSignage
		*  API. The function 'callback' is called after the
		*  API call has completed. True is passed as the first
		*  argument if the API call succeeds and false is
		*  passed otherwise.
		*/

		var slide = this;
		api_call(API_ENDP.SLIDE_GET, { 'id': id },
				function(response) {
			if (!response || response.error) {
				console.error("LibreSignage: API error!");
				if (callback) {
					callback(false);
				}
				return;
			}

			for (k in slide.REQ_KEYS) {
				slide.data[slide.REQ_KEYS[k]] =
					response[slide.REQ_KEYS[k]];
			}

			if (!slide._verify(slide.data)) {
				slide.data = {};
				if (callback) {
					callback(false);
				}
				return;
			}
			if (callback) {
				callback(true);
			}
		});
	}

	this.save = function(callback) {
		/*
		*  Save this slide using the LibreSignage API.
		*  The function 'callback' is called after the
		*  API call has completed. True is passed as the
		*  first argument if the API call was successful
		*  and false is passed otherwise.
		*/

		api_call(API_ENDP.SLIDE_SAVE, this.data,
				function(response) {
			if (!response || response.error) {
				console.error("LibreSignage: API error!");
				if (callback) {
					callback(false);
				}
				return;
			}
			if (callback) {
				callback(true);
			}
		});
	}

	this.set = function(data) {
		/*
		*  Copy the slide data from 'data' to this
		*  object's data dictionary. Only the valid
		*  data keys are copied. If required keys
		*  are missing from the 'data' dictionary, no
		*  data is copied and this function returns false.
		*  On success true is returned.
		*/

		if (!this._verify(data)) {
			return false;
		}

		for (k in this.REQ_KEYS) {
			this.data[this.REQ_KEYS[k]] =
				data[this.REQ_KEYS[k]];
		}

		return true;
	}

	this.remove = function(id, callback) {
		/*
		*  If 'id' is defined, remove the slide with
		*  ID 'id' using the LibreSignage API. Otherwise
		*  remove the currently loaded slide. Once the
		*  API call has completed, the function 'callback'
		*  is called. The callback function is passed true
		*  as the first argument if the API call succeeded
		*  and false is passed otherwise. 'callback' is also
		*  called with false if 'id' isn't defined and no slide
		*  is loaded.
		*/

		var r_id = "";
		if (id) {
			r_id = id;
		} else if (this.data.id) {
			r_id = this.data.id;
		} else {
			if (callback) {
				callback(false);
			}
		}

		api_call(API_ENDP.SLIDE_RM, { 'id': r_id },
				function(response) {
			if (!response || response.error) {
				console.error("LibreSignage: API error!");
				if (callback) {
					callback(false);
				}
				return;
			}
			if (callback) {
				callback(true);
			}
		})
	}

	this.clear = function() {
		this.data = {};
	}

	this.get = function(key) {
		return this.data[key];
	}
}
