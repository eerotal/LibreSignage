var $ = require('jquery');
var ls_queue = require('ls-queue');
var uic = require('ls-uicontrol');
var preview = require('./preview.js');

const TL_UPDATE_INTERVAL = 60000;

const timeline_btn = (id, index, name, enabled) => `
	<div class="btn tl-slide-cont ${!enabled ? 'tl-slide-cont-dis' : ''}"
		id="slide-btn-${id}">
		<div class="row m-0 p-0 h-100">
			<div class="col-2 tl-slide-index-cont">
				${index}
			</div>
			<div id="tl-slide-thumb-cont-${id}"
				class="col-10 tl-slide-thumb-cont">
			</div>
		</div>
	</div>
`;

exports.Timeline = class Timeline {
	/*
	*  LibreSignage Timeline UI element class.
	*
	*  api         = An initialized API object.
	*  f_sel_slide = A function to call when a slide is clicked.
	*                The ID of the slide that was clicked is passed
	*                to this function as the first argument. Note
	*                that you must call select() on the timeline object
	*                yourself if you want the selected slide highlighting
	*                to work.
	*/
	constructor(api, f_sel_slide) {
		this.selected = null;
		this.api = api;
		this.f_sel_slide = f_sel_slide;
		this.queue = null;

		this.TL = $("#timeline");
		this.TL_UI_DEFS = new uic.UIController({});

		this.update_html();
		setInterval(() => { this.update(); }, TL_UPDATE_INTERVAL);
	}

	update_html() {
		/*
		*  Update the timeline HTML elements.
		*/
		let s = null;
		let index = -1;

		this.TL.html('');
		this.TL_UI_DEFS.rm_all();

		if (!this.queue) { return; }
		while (s = this.queue.slides.next(index, false)) {
			index = s.get('index');
			
			let id_scoped = s.get('id');
			let s_scoped = s;
			
			this.TL.append(
				timeline_btn(
					s.get('id'),
					s.get('index'),
					s.get('name'),
					s.get('enabled')
				)
			);
			
			// Slide button UI definition.
			this.TL_UI_DEFS.add(id_scoped, new uic.UIButton(
				elem = $(`#slide-btn-${id_scoped}`),
				perm = () => { return s_scoped.get('enabled'); },
				enabler = (elem, s) => {
					if (s_scoped) {
						elem.removeClass('tl-slide-cont-dis');
					} else {
						elem.addClass('tl-slide-cont-dis');
					}
				},
				attach = {
					'click': () => {
						this.f_sel_slide(id_scoped);
					}
				},
				defer = null
			));

			// Thumb UI definition.
			this.TL_UI_DEFS.add(id_scoped, new uic.UIStatic(
				elem = new preview.Preview(
					`#tl-slide-thumb-cont-${id_scoped}`,
					null,
					() => { return s_scoped.get('markup'); },
					(e) => {
						if (e) {
							$(`#slide-btn-${id_scoped}`).addClass(
								'tl-slide-error'
							);
						} else {
							$(`#slide-btn-${id_scoped}`).removeClass(
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
			this.TL_UI_DEFS.get(id_scoped).get_elem().set_ratio(
				'16x9-fit'
			);
		}

		if (this.selected) {
			// Restyle the selected thumb.
			this.select(this.selected);
		}
	}

	update() {
		// Update timeline information and HTML.
		if (this.queue) {
			this.queue.update(() => { this.update_html(); });
		} else {
			this.update_html();
		}
	}

	show(name, ready) {
		if (!name) {
			this.queue = null;
			this.update_html();
			if (ready) { ready(); }
			return;
		}

		this.selected = null;
		this.queue = new ls_queue.Queue(this.api);
		this.queue.load(name, () => {
			this.update_html();
			if (ready) { ready(); }
		});
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
}
