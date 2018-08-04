var $ = require('jquery');
var markup = require('ls-markup');
var util = require('ls-util');

exports.Preview = class Preview {
	constructor(container, editor, getter) {
		this.container = $(container);
		this.preview = this.container.find('.preview');
		this.editor = $(editor);
		this.getter = getter;
		this.r = null;

		// Setup preview styling and metadata.
		this.preview.contents().find('head').append(
			`<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<link rel="stylesheet" href="/libs/bootstrap/dist/css/bootstrap.min.css"></link>
			<link rel="stylesheet" href="/common/css/default.css"></link>
			<link rel="stylesheet" href="/app/css/animations.css"></link>
			<link rel="stylesheet" href="/app/css/display.css"></link>
			<link rel="stylesheet" href="/common/css/markup.css"></link>`
		);
		this.editor.on('keyup', () => { this.update(); })
		this.ratio('16x9');
	}

	update() {
		var html = null;
		try {
			html = markup.parse(
				util.sanitize_html(
					this.getter()
				)
			);
		} catch (e) {
			if (!(e instanceof markup.MarkupParseError)) {
				throw e;
			}
		}

		if (html) {
			this.preview.contents().find('body').html(html);
		}
	}

	ratio(r) {
		if (r == '4x3') {
			this.container.removeClass('preview-16x9');
			this.container.addClass('preview-4x3');
			this.r = r;
		} else if (r == '16x9') {
			this.container.addClass('preview-16x9');
			this.container.removeClass('preview-4x3');
			this.r = r;
		}
	}
}
