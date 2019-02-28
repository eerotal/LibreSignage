var $ = require('jquery');

/*
*  Asset list thumbnail template. 'slide' is the slide
*  object to use and 'name' is the asset name.
*/
const asset_thumb_template = (slide, name) => `
<div class="thumb">
	<div class="thumb-inner default-border">
		<div class="thumb-img-wrapper">
			<img src="${slide.get_asset_thumb_uri(name)}"></img>
		</div>
		<div class="thumb-label-wrapper">
			<div class="thumb-rm-wrapper">
				<button class="btn btn-danger small-btn btn-remove"
						type="button">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="thumb-label">${name}</div>
		</div>
	</div>
</div>
`;

class AssetList {
	constructor(container) {
		this.container = container;
		this.slide = null;
	}

	show(slide) {
		/*
		*  Show the asset list for 'slide'.
		*/
		this.slide = slide;
		this.update();
	}

	hide() {
		/*
		*  Hide the asset list.
		*/
		this.slide = null;
		this.update();
	}

	update() {
		/*
		*  Update the asset list content.
		*/
		$(this.container).html('');
		if (this.slide == null) { return; }

		for (let a of this.slide.get('assets')) {
			let tmp = $(asset_thumb_template(this.slide, a.filename));
			$(this.container).append(tmp);

			tmp.on('click', () => {
				this.trigger(
					'select',
					{ name: a.filename }
				);
			});
			tmp.find('.btn-remove').on('click', e => {
				this.trigger(
					'remove',
					{ name: a.filename }
				);
				e.preventDefault();
			})
		}
	}

	trigger(name, data) {
		$(this.container).trigger(`component.assetlist.${name}`, data);
	}
}
exports.AssetList = AssetList;
