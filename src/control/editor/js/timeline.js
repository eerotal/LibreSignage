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
	*                This function should accept two arguments:
	*                The first one is the ID of the slide that was clicked.
	*                The second one is a function that should be called
	*                if the slide was indeed selected.
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
		let c_index = -1;
		let s = null;

		this.TL.html('');
		this.TL_UI_DEFS.rm_all();

		if (!this.queue) { return; }
		while (s = this.queue.slides.next(c_index, false)) {
			c_index = s.get('index');
			this.TL.append(
				timeline_btn(
					s.get('id'),
					s.get('index'),
					s.get('name'),
					s.get('enabled')
				)
			);
			let c_id = s.get('id');

			// Slide button UI definition.
			this.TL_UI_DEFS.add(c_id, new uic.UIButton(
				_elem = $(`#slide-btn-${c_id}`),
				_perm = () => { return s.get('enabled'); },
				_enabler = (elem, s) => {
					if (s) {
						elem.removeClass('tl-slide-cont-dis');
					} else {
						elem.addClass('tl-slide-cont-dis');
					}
				},
				_attach = {
					'click': () => {
						this.f_sel_slide(c_id, () => {
							this.select(c_id);
						});
					}
				},
				_defer = null
			));

			// Thumb UI definition.
			this.TL_UI_DEFS.add(c_id, new uic.UIStatic(
				_elem = new preview.Preview(
					`#tl-slide-thumb-cont-${c_id}`,
					null,
					() => { return s.get('markup'); },
					(e) => {
						if (e) {
							$(`#slide-btn-${c_id}`).addClass(
								'tl-slide-error'
							);
						} else {
							$(`#slide-btn-${c_id}`).removeClass(
								'tl-slide-error'
							);
						}
					},
					true
				),
				_perm = () => { return true; },
				_enabler = () => {},
				_attach = null,
				_defer = null,
				_getter = () => {},
				_setter = () => {}
			));
			this.TL_UI_DEFS.get(c_id).get_elem().set_ratio('16x9-fit');
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
