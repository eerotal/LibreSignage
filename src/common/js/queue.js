/*
*  Queue object definition for interfacing with LibreSignage
*  slide queues via the API. The Queue class uses the SlideList
*  class for storing slides, which makes manipulating slide lists
*  very easy.
*/

class Queue {
	constructor() {
		this.name = null;
		this.slides = null;
	}

	load(name, ready) {
		var tmp = null;
		var cnt = 0;
		var id = '';

		this.name = name;
		this.slides = new SlideList();

		api_call(API_ENDP.QUEUE_GET, {'name': name}, (data) => {
			if (api_handle_disp_error(data.error)) {
				return;
			}

			cnt = Object.keys(data['slides']).length;

			if (cnt == 0 && ready) {
				// Execute callback for empty queues.
				ready(this);
				return
			}

			for (let s of Object.values(data['slides'])) {
				id = s['id'];
				this.slides.slides[id] = new Slide();
				this.slides.slides[id].load(id, () => {
					if (--cnt == 0 && ready) {
						ready(this);
					}
				});
			}
		})
	}

	update(ready) {
		this.load(this.name, ready);
	}
}
