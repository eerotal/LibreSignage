var markup = require('ls-markup');
var Assert = require('assert');

/**
* Slide preview component.
*/
class Preview {
	/**
	* Construct a new Slide preview.
	*
	* @param {HTMLElement} container The HTML element where the Preview
	*                                is created.
	*/
	constructor(container) {
		this.valid_ratios = ['4x3', '16x9', '4x3-fit', '16x9-fit'];

		this.container = container;
		this.ratio = null;
	}

	/**
	* Initialize a new preview.
	*
	* @return {Promise} A promise that resolves once all stylesheets
	*                   are loaded.
	*/
	async init() {
		let ret = null;
		let template = document.createElement('template');
		template.innerHTML = '<iframe class="preview rounded"></iframe>';

		/*
		* NOTE!
		*
		* Firefox doesn't allow adding content to iframes before a load
		* event is fired on them, which is why the code below is wrapped
		* in an event listener. Other browsers, however, don't even fire
		* the load event so the event is fired manually after creating
		* the event listener.
		*/
		ret = new Promise((resolve, reject) => {
			let iframe = template.content.querySelector('iframe');

			let hook = () => {
				let head = null;
				let promises = [];

				/*
				* Workaround for odd Firefox behaviour where the 'load'
				* event is fired twice for the iframe. Once when the
				* document readyState == 'uninitialized' and again when
				* readyState == 'complete'. This if statement skips the
				* uninitialized step to make the modified iframe contents
				* actually persist.
				*/
				if (iframe.contentDocument.readyState !== 'complete') {
					return;
				}
				iframe.removeEventListener('load', hook);

				// Add meta tags and stylesheets.
				head = iframe.contentDocument.querySelector('head');
				head.innerHTML = `
					<meta
						charset="utf-8">
					<meta
						name="viewport"
						content="width=device-width, initial-scale=1">
					<link
						rel="stylesheet"
						type="text/css"
						href="/app/css/display.css"></link>
				`;

				for (let e of head.querySelectorAll('link')) {
					promises.push(new Promise((resolve, reject) => {
						e.addEventListener('load', resolve);
					}));
				}

				this.set_ratio('16x9');
				Promise.all(promises).then(resolve);
			};

			iframe.addEventListener('load', hook);
		});
		this.container.appendChild(template.content);

		if (
			window.navigator === null
			|| window.navigator.userAgent === null
			|| !window.navigator.userAgent.match('/mozilla/i')
		) {
			this.container
				.querySelector('iframe')
				.dispatchEvent(new Event('load'));
		}
		return ret;
	}

	/**
	* Render a Preview.
	*
	* @param {string} m The markup to render.
	*/
	render(m) {
		let html = markup.parse(m);
		let template = null;

		if (html != null) {
			template = document.createElement('template');
			template.innerHTML = html;

			// Don't autoplay video.
			for (let e of template.content.querySelectorAll('video')) {
				e.setAttribute('autoplay', false);
			}

			this.container
				.querySelector('iframe')
				.contentDocument
				.body
				.innerHTML = "";
			this.container
				.querySelector('iframe')
				.contentDocument
				.querySelector('body')
				.appendChild(document.importNode(template.content, true))
		}
	}

	/**
	* Set the aspect ratio of a Preview.
	*
	* @param {string} ratio The ratio to use. This should be one of the strings
	*                       defined in Preview.valid_ratios.
	*
	* @throws {AssertionError} If the supplied ratio is not valid.
	*/
	set_ratio(ratio) {
		Assert.ok(this.valid_ratios.includes(ratio));
		this.ratio = ratio;

		for (let r of this.valid_ratios) {
			this.container.classList.remove(`preview-${r}`);
		}
		this.container.classList.add(`preview-${ratio}`);
	}

	/**
	* Get the current aspect ratio of a Preview.
	*
	* @return {string} The current aspect ratio.
	*/
	get_ratio() {
		return this.ratio;
	}
}
module.exports = Preview;
