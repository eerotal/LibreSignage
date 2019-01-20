var $ = require('jquery');
var markup = require('ls-markup');
var util = require('ls-util');

const META = [
	{ charset: 'utf-8' },
	{ name: 'viewport', content: 'width=device-width, initial-scale=1' }
];
const STYLESHEETS = [ '/app/css/display.css' ];

class Preview {
	constructor(container_id) {
		this.container = $(`#${container_id}`);
	}

	async init() {
		let ret = null;

		// Add the preview iframe to the container.
		this.preview = $('<iframe class="preview rounded"></iframe>');

		/*
		*  NOTE!
		*
		*  Firefox doesn't allow adding content to iframes before a load
		*  event is fired on them, which is why the code below is wrapped
		*  in an event listener. Other browsers, however, don't even fire
		*  the load event so the event is fired manually after creating
		*  the event listener.
		*/
		ret = new Promise((resolve, reject) => {
			this.preview.one('load', () => {
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
				Promise.all(promises).then(resolve);
			})
		});

		this.container.append(this.preview);
		if (
			window.navigator === null
			|| window.navigator.userAgent === null
			|| !window.navigator.userAgent.match('/mozilla/i')
		) {
			this.preview.trigger('load');
		}
		return ret;
	}

	render(m) {
		let content = null;
		let html = markup.parse(util.sanitize_html(m));

		if (html != null) {
			content = $(html);

			// Don't autoplay video.
			if (content.is('video')) {
				content.removeAttr('autoplay');
			}
			content.find('video').removeAttr('autoplay');

			this.preview.contents().find('body').html(content);
		}
	}

	set_ratio(r) {
		/*
		*  Set the aspect ratio of the preview box.
		*/
		this.container.removeClass(
			'preview-16x9 preview-4x3 preview-16x9-fit preview-4x3-fit'
		);
		switch(r) {
			case '4x3':
			case '16x9':
			case '4x3-fit':
			case '16x9-fit':
				this.container.addClass(`preview-${r}`);
				break;
			default:
				throw new Error(`Unknown aspect ratio '${r}'.`);
				break;
		}
	}
}
exports.Preview = Preview;
