var TIMELINE_UPDATE_INTERVAL = 60000;
var TIMELINE = $("#timeline");
var TIMELINE_THUMB_SEL = '.tl-slide-thumb';

var timeline_queue = null;

const timeline_btn = (id, index, name, enabled) => `
	<div class="btn tl-slide-cont ${!enabled ? 'tl-slide-cont-dis' : ''}"
		id="slide-btn-${id}"
		onclick="slide_show('${id}')">
		<div class="row m-0 p-0 h-100">
			<div class="col-2 tl-slide-index-cont">
				${index}
			</div>
			<div class="col-10 tl-slide-thumb-cont">
				<iframe class="tl-slide-thumb"
					src="/app?preview=${id}&noui=1"
					frameborder="0">
				</iframe>
			</div>
		</div>
	</div>
`;

function timeline_update() {
	/*
	*  Update timeline information and HTML.
	*/
	if (timeline_queue) {
		timeline_queue.update(timeline_update_html);
	} else {
		timeline_update_html();
	}
}

function timeline_update_html() {
	/*
	*  Update timeline HTML.
	*/
	var c_i = -1;
	var slide = null;
	var list = null;

	if (!timeline_queue) {
		TIMELINE.html('');
		return;
	}

	list = timeline_queue.slides;

	TIMELINE.html('');
	while (slide = list.next(c_i, false)) {
		c_i = slide.get('index');
		TIMELINE.append(
			timeline_btn(
				slide.get('id'),
				slide.get('index'),
				slide.get('name'),
				slide.get('enabled')
			)
		);
	}
	console.log("LibreSignage: Disable logging for thumbs.");
	$(TIMELINE_THUMB_SEL).each(function() {
		this.contentWindow.console.log = function() {};
		this.contentWindow.console.warn = function() {};
		this.contentWindow.console.error = function() {};
	});
}

function timeline_show(queue) {
	if (!queue) {
		timeline_queue = null;
		timeline_update_html();
		return;
	}
	timeline_queue = new Queue();
	timeline_queue.load(queue, timeline_update_html);
}

function timeline_setup() {
	setInterval(timeline_update, TIMELINE_UPDATE_INTERVAL);
}
