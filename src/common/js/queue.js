/*
*  Queue object definition for interfacing with LibreSignage
*  slide queues via the API.
*/

function Queue() {
	this.name = null;
	this.slides = null;

	this.load = function(name, ready) {
		var tmp = null;
		var cnt = 0;
		var id = '';
		api_call(API_ENDP.QUEUE_GET, {'name': name}, (data) => {
			if (api_handle_disp_error(data.error)) {
				return;
			}

			this.name = name;
			this.slides = {};

			cnt = Object.keys(data['slides']).length;
			for (let s of Object.values(data['slides'])) {
				id = s['id'];
				this.slides[id] = new Slide();
				this.slides[id].load(id, () => {
					if (--cnt == 0 && ready) {
						ready(this);
					}
				});
			}
		})
	}

	this.update = function(ready) {
		this.load(this.name, ready);
	}

	this.filter = function(filter) {
		var ret = {};
		var add;
		for (var s in this.slides) {
			add = true;
			for (var k in filter) {
				if (this.slides[s].data[k] != filter[k]) {
					add = false;
					break;
				}
			}
			if (add) {
				ret[s] = this.slides[s];
			}
		}
		return ret;
	}

	this.get = function() {
		return this.slides;
	}

	this.length = function() {
		return Object.keys(this.slides).length;
	}
}
