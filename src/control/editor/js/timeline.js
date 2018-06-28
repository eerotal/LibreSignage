var TIMELINE_UPDATE_INTERVAL = 60000;
var TIMELINE = $("#timeline");
var TIMELINE_THUMB_SEL = '.slide-thumb';

var timeline_queue = null;

const timeline_btn = (id, index, name, enabled) => `
	<div class="btn slide-cont ${!enabled ? 'slide-cont-dis' : ''}"
		id="slide-btn-${id}"
		onclick="slide_show('${id}')">
		<div class="row m-0 p-0 h-100">
			<div class="col-2 slide-index-cont">
				${index}
			</div>
			<div class="col-10 slide-thumb-cont">
				<iframe class="slide-thumb"
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
	timeline_queue.update(timeline_update_html);
}

function timeline_update_html() {
	/*
	*  Update timeline HTML.
	*/
	var c_i = -1;
	var slide = null;
	var list = timeline_queue.slides;

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

function timeline_setup(queue) {
	timeline_queue = new Queue();
	timeline_queue.load(queue);
	timeline_update();
	setInterval(timeline_update, TIMELINE_UPDATE_INTERVAL);
}
