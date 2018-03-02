/*
*  LibreSignage JS Slide object definition with functions
*  for loading data through the LibreSignage API.
*/

var SLIDE_REQ_KEYS = [
	'id',
	'name',
	'owner',
	'time',
	'index',
	'markup'
];

function Slide() {
	this.data = {};

	this._verify = function(data) {
		/*
		*  Verify the slide data in 'data'.
		*/
		for (k in SLIDE_REQ_KEYS) {
			if (!(SLIDE_REQ_KEYS[k] in data)) {
				return false;
			}
		}
		return true;
	}

	this.load = function(id, callback) {
		/*
		*  Load the slide with ID 'id' using the LibreSignage
		*  API. The function 'callback' is called after the
		*  API call has completed. An API error code is passed
		*  to the callback function as the first argument. The
		*  API_E.CLIENT error code is used for indicating
		*  non-API errors.
		*/
		api_call(API_ENDP.SLIDE_GET, { 'id': id }, (resp) => {
			if (resp.error) {
				console.error("LibreSignage: API error!");
				if (callback) {
					callback(resp.error);
				}
				return;
			}

			for (k in SLIDE_REQ_KEYS) {
				this.data[SLIDE_REQ_KEYS[k]] =
					resp[SLIDE_REQ_KEYS[k]];
			}

			if (!this._verify(this.data)) {
				this.data = {};
				if (callback) {
					callback(API_E.CLIENT);
				}
				return;
			}

			if (callback) {
				callback(resp.error);
			}
		});
	}

	this.save = function(callback) {
		/*
		*  Save this slide using the LibreSignage API. The
		*  function 'callback' is called after the API call
		*  has completed. An API error code is passed to the
		*  callback function as the first argument. The
		*  API_E.CLIENT error code is used for indicating
		*  non-API errors.
		*/
		api_call(API_ENDP.SLIDE_SAVE, this.data, (resp) => {
			if (resp.error) {
				console.error("LibreSignage: API error!");
				if (callback) {
					callback(resp.error);
				}
				return;
			}

			if (!this._verify(resp)) {
				console.error("LibreSignage: " +
					"Invalid API response.");
				if (callback) {
					callback(API_E.CLIENT);
				}
				return;
			}

			this.set(resp);
			if (callback) {
				callback(resp.error);
			}
		});
	}

	this.set = function(data) {
		/*
		*  Copy the slide data from 'data' to this
		*  object's data dictionary overwriting existing
		*  data. After the data has been set, this
		*  function returns true if the data in this object
		*  is valid and false otherwise.
		*/

		for (k in SLIDE_REQ_KEYS) {
			if (SLIDE_REQ_KEYS[k] in data) {
				this.data[SLIDE_REQ_KEYS[k]] =
					data[SLIDE_REQ_KEYS[k]];
			}
		}
		return this._verify(this.data);
	}

	this.remove = function(id, callback) {
		/*
		*  If 'id' is defined, remove the slide with the
		*  ID 'id' using the LibreSignage API. Otherwise
		*  remove the currently loaded slide. The function
		*  'callback' is called after the API call has
		*  completed. An API error code is passed to the
		*  callback function as the first argument. The
		*  API_E.CLIENT error code is used for indicating
		*  non-API errors.
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
		})
	}

	this.clear = function() {
		this.data = {};
	}

	this.get = function(key) {
		return this.data[key];
	}
}
