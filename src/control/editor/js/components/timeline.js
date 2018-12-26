var $ = require('jquery');
var Preview = require('./preview.js').Preview;
var MarkupError = require('ls-markup').err.MarkupError;

const slide_template = s => `
<div
	class="btn tl-slide-cont ${!s.get('enabled') ? 'disabled': ''}"
	id="tl-slide-btn-${s.get('id')}">
	<div class="tl-slide-index-cont">${s.get('index')}</div>
	<div class="tl-slide-thumb-cont preview-cont"
		id="tl-slide-thumb-cont-${s.get('id')}">
	</div>
</div>
`;

class Timeline {
	constructor(container_id) {
		this.container = $(`#${container_id}`);

		this.queue = null;
		this.slide = null;

		this.thumbs = null;
	}

	slide_clicked(id) {
		/*
		*  Slide click handler function.
		*/
		if (
			this.slide != null
			&& this.slide.get('id') === id
		) { return; }

		this.container.trigger(
			'component.timeline.click',
			{ 'id': id }
		);

		let slides = this.queue.get_slides().get_slides();
		for (let s of Object.values(slides)) {
			if (s.get('id') === id) {
				this.slide = s;
				$(`#tl-slide-btn-${s.get('id')}`).addClass('selected');
			} else {
				$(`#tl-slide-btn-${s.get('id')}`).removeClass('selected');
			}
		}
	}

	async show_queue(queue) {
		/*
		*  Show a queue and setup the necessary event listeners.
		*/
		let i = -1;
		let s = null;

		this.queue = queue;
		this.slide = null;
		this.container.html('');
		this.thumbs = {};

		while (s = queue.get_slides().next(i++, false)) {
			let id = s.get('id');
			let thumb = null;

			this.container.append(slide_template(s));
			$(`#tl-slide-btn-${id}`).on('click', () => {
				this.slide_clicked(id);
			});

			thumb = new Preview(`tl-slide-thumb-cont-${id}`);
			await thumb.init();

			try {
				thumb.render(s.get('markup'));
			} catch (e) {
				if (e instanceof MarkupError) {
					$(`#tl-slide-btn-${id}`).addClass('error');
				} else {
					$(`#tl-slide-btn-${id}`).removeClass('error');
					throw e;
				}
			}
			this.thumbs[id] = thumb;
		}
	}

	hide_queue() {
		/*
		*  Hide a queue.
		*/
		this.queue = null;
		this.slide = null;
		this.thumbs = null;
		this.container.html('');
	}
}
exports.Timeline = Timeline;
