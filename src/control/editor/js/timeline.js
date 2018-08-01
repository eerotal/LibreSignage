var $ = require('jquery');
var ls_queue = require('ls-queue');
var uic = require('ls-uicontrol');

const TL_UPDATE_INTERVAL = 60000;

const timeline_btn = (id, index, name, enabled) => `
	<div class="btn tl-slide-cont ${!enabled ? 'tl-slide-cont-dis' : ''}"
		id="slide-btn-${id}">
		<div class="row m-0 p-0 h-100">
			<div class="col-2 tl-slide-index-cont">
				${index}
			</div>
			<div class="col-10 tl-slide-thumb-cont">
				<iframe class="tl-slide-thumb"
					src="/app?preview=${id}&noui=1&silent=1"
					frameborder="0">
				</iframe>
			</div>
		</div>
	</div>
`;

exports.Timeline = class Timeline {
	constructor(api, func_select_slide) {
		this.selected = null;
		this.api = api;
		this.func_select_slide = func_select_slide;
		this.queue = null;

		this.TL = $("#timeline");
		this.TL_UI_DEFS = new uic.UIController({});

		setInterval(() => { this.update(); }, TL_UPDATE_INTERVAL);
	}

	update_html() {
		/*
		*  Update timeline HTML.
		*/
		var c_index = -1;
		var c_id = null;
		var s = null;

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
			let c_id = s.get('id'); // Solves variable referencing.
			this.TL_UI_DEFS.add(c_id, new uic.UIButton(
				_elem = $(`#slide-btn-${c_id}`),
				_perm = () => { return true; },
				_enabler = () => {},
				_attach = {
					'click': () => {
						this.select(c_id);
						this.func_select_slide(c_id);
					}
				},
				_defer = null
			));
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
		$(`#slide-btn-${id}`).addClass('tl-selected');
		this.selected = id;
	}
}
