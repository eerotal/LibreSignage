var Preview = require('../preview/Preview.js');
var MarkupError = require('ls-markup').err.MarkupError;

/**
* Editor timeline component.
*/
class Timeline {
	/**
	* Construct a new Timeline object.
	*
	* @param {HTMLElement} container The HTML element where the Timeline
	*                                is created.
	*/
	constructor(container) {
		this.container = container;
		this.queue = null;
		this.slide = null;
		this.thumbs = null;
		this.last_clicked_slide_id = null;
	}

	/**
	* Create a div node for the Slide "frames" in the Timeline.
	*
	* @param {Slide} s The Slide to create the node for.
	*
	* @param {HTMLElement} The created frame node.
	*/
	static make_slide_frame_node(s) {
		let div = document.createElement('div');

		div.classList.add('btn', 'tl-slide-cont');
		if (!s.get('enabled')) {
			div.classList.add('disabled');
		}

		div.id = `tl-slide-btn-${s.get('id')}`;
		div.innerHTML = `
			<div class="tl-slide-index-cont">${s.get('index')}</div>
			<div
				class="tl-slide-thumb-cont preview-cont"
				id="tl-slide-thumb-cont-${s.get('id')}">
			</div>
		`;

		return div;
	}

	/**
	* Slide click handler function.
	*
	* @param {string} id The ID of the Slide that was clicked.
	*/
	slide_clicked(id) {
		if (
			this.slide != null
			&& this.slide.get('id') === id
		) { return; }

		this.last_clicked_slide_id = id;
		this.container.dispatchEvent(new Event('component.timeline.click'));
	}

	/**
	* Select a slide from a Timeline.
	*
	* @param {string} id The ID of the Slide to select.
	*/
	set_selected(id) {
		for (let s of [...this.queue.get_slidelist()]) {
			let cl = this.container
				.querySelector(`#tl-slide-btn-${s.get('id')}`)
				.classList;

			if (s.get('id') === id) {
				this.slide = s;
				cl.add('selected');
			} else {
				cl.remove('selected');
			}
		}
	}

	/**
	* Show a Queue in a Timeline.
	*
	* @param {Queue} queue The Queue object to show.
	*/
	async show_queue(queue) {
		this.queue = queue;
		this.slide = null;
		this.container.innerHTML = '';
		this.thumbs = {};

		for (let s of [...this.queue.get_slidelist()]) {
			let div = Timeline.make_slide_frame_node(s);
			this.container.appendChild(div);

			div.addEventListener('click', () => {
				this.slide_clicked(s.get('id'));
			});

			let thumb = new Preview(div.querySelector(
				`#tl-slide-thumb-cont-${s.get('id')}`
			));
			await thumb.init();

			try {
				thumb.render(s.get('markup'));
			} catch (e) {
				let cl = this.container
					.querySelector(`#tl-slide-btn-${s.get('id')}`)
					.classList;

				if (e instanceof MarkupError) {
					cl.add('error');
				} else {
					cl.remove('error');
					throw e;
				}
			}
			this.thumbs[s.get('id')] = thumb;
		}
	}

	/**
	* Update a Timeline.
	*
	* @param {bool} preserve_selected If true, the current slide selection
	*                                 is preserved.
	*/
	async update(preserve_selected) {
		let slide = this.slide;
		await this.queue.update();
		await this.show_queue(this.queue);

		if (slide != null && preserve_selected === true) {
			this.set_selected(slide.get('id'));
		}
	}

	/**
	* Hide a queue.
	*/
	hide_queue() {
		this.queue = null;
		this.slide = null;
		this.thumbs = null;
		this.container.innerHTML = '';
	}

	/**
	* Get the ID of the Slide that was last clicked.
	*
	* @param {string} A Slide ID.
	*/
	get_last_clicked_slide_id() {
		return this.last_clicked_slide_id;
	}
}
module.exports = Timeline;
