var UIUtil = require('libresignage/util/UIUtil');
var Assert = require('assert');

/**
* A class representing the list of assets in an AssetUploader window.
*/
class AssetList {
	/**
	* Construct a new AssetList object.
	*
	* @param {HTMLElement} container The container element where the
	*                                AssetList is created.
	*/
	constructor(container) {
		this.container = container;
		this.slide = null;
		this.selected = null;
	}

	/**
	* Create a HTML DIV node for a thumbnail.
	*
	* @param {string} asset_name The name of the asset.
	* @param {string} url        The URL of the asset thumbnail.
	*
	* @return {HTMLElement} The created HTML DOM node.
	*/
	static make_thumb_node(asset_name, url) {
		let div = document.createElement('div');
		div.classList.add('thumb');
		div.innerHTML = `
			<div class="thumb-inner default-border">
				<div class="thumb-img-wrapper">
					<img src="${url}"></img>
				</div>
				<div class="thumb-label-wrapper">
					<div class="thumb-rm-wrapper">
						<button class="btn btn-danger small-btn btn-remove"
								type="button">
							<i class="fas fa-times"></i>
						</button>
					</div>
					<div class="thumb-label">${asset_name}</div>
				</div>
			</div>
		`;
		return div;
	}

	/**
	* Show the asset list for a slide.
	*
	* @param {Slide} slide The slide to show the asset list for.
	*/
	show(slide) {
		this.slide = slide;
		this.update();
	}

	/**
	* Hide the asset list.
	*/
	hide() {
		this.slide = null;
		this.update();
	}

	/**
	* Get the selected filename.
	*
	* @return {string|null} The filename or null if nothing is selected.
	*/
	get_selection() {
		return this.selected;
	}

	/**
	* Deselect the current selection.
	*/
	deselect() {
		this.selection = null;
	}

	/**
	* Update the asset list content.
	*/
	update() {
		this.container.innerHTML = '';
		if (this.slide == null) { return; }

		for (let a of Object.values(this.slide.get('assets'))) {
			let url = null;

			// Use the asset thumbnail if it exists and a placeholder otherwise.
			if (this.slide.get('assets')[a.filename].has_thumb()) {
				url = this.slide.get('assets')[a.filename].get_thumb_url();
			} else {
				url = UIUtil.fa_svg_uri('solid', 'image');
			}

			let div = AssetList.make_thumb_node(a.filename, url);
			this.container.appendChild(div);

			// Attach event listeners for the select and remove actions.
			div.addEventListener('click', () => {
				this.selected = a.filename;
				this.container.dispatchEvent(
					new Event('component.assetlist.select')
				);
			});
			div.querySelector('.btn-remove')
				.addEventListener('click', e => {
					this.container.dispatchEvent(
						new Event('component.assetlist.remove')
					);
					e.preventDefault();
				});
		}
	}
}
module.exports = AssetList;
