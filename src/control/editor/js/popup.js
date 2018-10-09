var $ = require('jquery');
var assert = require('ls-assert');

const popup_content_wrapper = (id, content) => `
<div class="row">
	<div class="col-12 container text-right">
		<i id="close-popup-${id}"
			class="fas fa-times-circle link-nostyle">
		</i>
	</div>
</div>
<div class="row">
	<div id="popup-content-${id}" class="col-12 container popup-content">
		${content}
	</div>
</div>	
`;

module.exports.Popup = class Popup {
	constructor(element) {
		assert.assert(element !== null);
		assert.assert(element.id !== null);

		this.element = element;

		// Wrap the popup content in the wrapper HTML.
		$(this.element).html(
			popup_content_wrapper(
				this.element.id,
				this.element.innerHTML
			)
		);
		$(`#close-popup-${this.element.id}`).click(() => {
			this.visible(false);
		});
	}

	visible(state) {
		if (state) {
			$(this.element).css('display', 'block');
		} else {
			$(this.element).css('display', 'none');
		}
	}
}
