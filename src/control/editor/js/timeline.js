var $ = require('jquery');
var Queue = require('ls-queue').Queue;
var APIUI = require('ls-api-ui');

var uic = require('ls-uicontrol');
var preview = require('./preview.js');

const TL_UPDATE_INTERVAL = 60000;

const timeline_btn = (id, index, name, enabled) => `
<div class="btn tl-slide-cont ${!enabled ? 'tl-slide-cont-dis' : ''}"
	id="slide-btn-${id}">
	<div class="m-0 p-0 h-100">
		<div class="tl-slide-index-cont">${index}</div>
		<div id="tl-slide-thumb-cont-${id}" 
			class="tl-slide-thumb-cont preview-cont">
		</div>
	</div>
</div>
`.replace(/\t*\n\t*/g, '');

class Timeline {
	/*
	*  LibreSignage Timeline UI element class.
	*
	*  api         = An initialized API object.
	*  container   = A JQuery selector string used for selecting the
	*                timeline HTML container.
	*  onclick     = A function to call when a slide is clicked.
	*                The ID of the slide that was clicked is passed
	*                to this function as the first argument. Note
	*                that you must call select() on the timeline object
	*                yourself if you want the selected slide highlighting
	*                to work.
	*/
	constructor(api, container, onclick) {
		this.api = api;
		this.selected = null;
		this.onclick = onclick;
		this.queue = null;

		this.TL = $(container);
		this.UI = new uic.UIController({});

		this.update_html();
		setInterval(async () => {
			try {
				await this.update();
			} catch (e) {
				APIUI.handle_error(e);
			}
		}, TL_UPDATE_INTERVAL);
	}

	update_html() {
		/*
		*  Update the timeline HTML elements.
		*/
		let tmp = null;
		let index = -1;

		this.TL.html('');
		this.UI.rm_all();

		if (!this.queue) { return; }
		while (tmp = this.queue.get_slides().next(index, false)) {
			let s = tmp; // Handle problems with scopes.
			let id = s.get('id');
			index = s.get('index');

			this.TL.append(
				timeline_btn(
					id,
					index,
					s.get('name'),
					s.get('enabled')
				)
			);
			
			// Slide button UI definition.
			this.UI.add(id, new uic.UIButton(
				elem = $(`#slide-btn-${id}`),
				perm = () => { return s.get('enabled'); },
				enabler = (elem, s) => {
					if (s) {
						elem.removeClass('tl-slide-cont-dis');
					} else {
						elem.addClass('tl-slide-cont-dis');
					}
				},
				attach = {
					'click': () => { this.onclick(id); }
				},
				defer = null
			));

			// Thumb UI definition.
			this.UI.add(id, new uic.UIStatic(
				elem = new preview.Preview(
					`#tl-slide-thumb-cont-${id}`,
					null,
					() => { return s.get('markup'); },
					(e) => {
						if (e) {
							$(`#slide-btn-${id}`).addClass(
								'tl-slide-error'
							);
						} else {
							$(`#slide-btn-${id}`).removeClass(
								'tl-slide-error'
							);
						}
					},
					true
				),
				perm = () => { return true; },
				enabler = () => {},
				attach = null,
				defer = null,
				getter = () => {},
				setter = () => {}
			));
			this.UI.get(id).get_elem().set_ratio(
				'16x9-fit'
			);
		}

		if (this.selected) {
			// Restyle the selected thumb.
			this.select(this.selected);
		}
	}

	async update() {
		// Update queue and UI.
		if (this.queue) {
			await this.queue.update();
		}
		this.update_html();
	}

	async show(name) {
		this.selected = null;
		if (!name) {
			this.queue = null;
			this.update_html();
			return;
		}

		this.queue = new Queue(this.api);
		await this.queue.load(name);
		this.update_html();
	}

	select(id) {
		if (this.selected) {
			$(`#slide-btn-${this.selected}`).removeClass('tl-selected');
		}
		if (id) {
			$(`#slide-btn-${id}`).addClass('tl-selected');
		}
		this.selected = id;
	}

	get_queue() { return this.queue; }
}
exports.Timeline = Timeline;
