var $ = require('jquery');

const slide_template = s => `
<div
	class="btn tl-slide-cont ${!s.get('enabled') ? 'disabled': ''}"
	id="slide-btn-${s.get('id')}">
	<div class="m-0 p-0 h-100">
		<div
			class="tl-slide-index-cont">
			${s.get('index')}
		</div>
		<div
			class="tl-slide-thumb-cont preview-cont"
			id="tl-slide-thumb-cont-${s.get('id')}">
		</div>
	</div>
</div>
`;

class Timeline {
	constructor(api, container_id) {
		this.api = api;
		this.container = $(`#${container_id}`);

		this.queue = null;
		this.slide = null;
	}

	slide_clicked(id) {
		/*
		*  Slide click handler function.
		*/
		if (
			this.slide != null
			&& this.slide.get('id') === id
		) { return; }

		this.container[0].dispatchEvent(
			new CustomEvent(
				'timeline.click',
				{ 'id': id }
			)
		);

		let slides = this.queue.get_slides().get_slides();
		for (let s of Object.values(slides)) {
			if (s.get('id') === id) {
				this.slide = s;
				$(`#slide-btn-${s.get('id')}`).addClass('selected');
			} else {
				$(`#slide-btn-${s.get('id')}`).removeClass('selected');
			}
		}
	}

	show_queue(queue) {
		/*
		*  Show a queue and setup the necessary event listeners.
		*/
		let i = -1;
		let s = null;

		this.queue = queue;
		this.container.html('');

		while (s = queue.get_slides().next(i++, false)) {
			let id = s.get('id');
			this.container.append(slide_template(s));
			$(`#slide-btn-${id}`).on('click', () => {
				this.slide_clicked(id);
			});
		}
	}

	hide_queue() {
		/*
		*  Hide a queue.
		*/
		this.queue = null;
		this.slide = null;
		this.container.html('');
	}
}
exports.Timeline = Timeline;
