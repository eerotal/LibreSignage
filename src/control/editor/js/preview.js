/*
*  LibreSignage live slide preview implementation.
*/

var $ = require('jquery');
var markup = require('ls-markup');
var util = require('ls-util');

const META = [
	{ charset: 'utf-8' },
	{ name: 'viewport', content: 'width=device-width, initial-scale=1' }
];
const STYLESHEETS = [ '/app/css/display.css' ];

exports.Preview = class Preview {
	constructor(container, editor, getter, err, noevent) {
		this.container = $(container);
		this.editor = $(editor);
		this.getter = getter;
		this.onevent = noevent;
		this.err = err;
		this.ratio = null;

		// Add the preview iframe to the container.
		this.preview = $('<iframe class="preview rounded"></frame>');
		this.container.append(this.preview);

		/*
		*  NOTE!
		*
		*  Firefox doesn't allow adding content to iframes before a load
		*  event is fired on them, which is why the code below is wrapped
		*  in an event listener. Other browsers, however, don't even fire
		*  the load event so the event is fired manually after creating
		*  the event listener.
		*/
		this.preview.on('load', () => {
			// Add meta tags.
			let phead = this.preview.contents().find('head');
			for (let m of META) { phead.append($('<meta>').attr(m)); }

			// Add stylesheets.
			let tmp = null;
			let promises = [];
			for (let s of STYLESHEETS) {
				tmp = $('<link></link>').attr(
					{
						'rel': 'stylesheet',
						'href': s
					}
				);
				promises.push(new Promise(
					(resolve, reject) => {
						tmp.on('load', resolve);
					}
				));
				phead.append(tmp);
			}
			this.set_ratio('16x9');

			/*
			*  Run the rest of the setup code once the stylesheets have
			*  loaded. This prevents the user from seeing a preview with
			*  no styling.
			*/
			Promise.all(promises).then(() => {
				if (!this.noevent) {
					this.editor.on('keyup', () => { this.update(); })
				}
				this.update();
			});
		});

		if (
			window.navigator === null
			|| window.navigator.userAgent === null
			|| !window.navigator.userAgent.match('/mozilla/i')
		) {
			this.preview.trigger('load');
		}
	}

	update() {
		/*
		*  Update the contents of the preview box.
		*/
		var html = null;

		// Clear previous errors.
		if (this.err) { this.err(null); }
		try {
			html = markup.parse(
				util.sanitize_html(
					this.getter()
				)
			);
		} catch (e) {
			if (e instanceof markup.err.MarkupError) {
				if (this.err) { this.err(e); }
			} else {
				throw e;
			}
		}

		if (html != null) {
			let content = $(html);

			/* 
			*  Disable autoplaying video for previews.
			*/
			if (content.is('video')) {
				content.removeAttr('autoplay');
			}
			content.find('video').removeAttr('autoplay');

			this.preview.contents().find('body').html(content);
		}
	}

	set_ratio(r) {
		/*
		*  Set the aspect ratio of the preview box. Accepted
		*  values for r are '16x9' and '4x3'.
		*/
		this.container.removeClass(
			'preview-16x9 preview-4x3 preview-16x9-fit preview-4x3-fit'
		);
		if (r == '4x3') {
			this.container.addClass('preview-4x3');
			this.ratio = r;
		} else if (r == '16x9') {
			this.container.addClass('preview-16x9');
			this.ratio = r;
		} else if (r == '4x3-fit') {
			this.container.addClass('preview-4x3-fit');
			this.ratio = r;
		} else if (r == '16x9-fit') {
			this.container.addClass('preview-16x9-fit');
			this.ratio = r;
		} else {
			throw new Error(`Unknown aspect ratio '${r}'.`);
		}
	}
}
