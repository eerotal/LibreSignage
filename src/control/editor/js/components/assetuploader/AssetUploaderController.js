var Assert = require('assert');

/**
* Controller class for an AssetUploader popup.
*/
class AssetUploaderController {
	/**
	* Construct a new AssetUploaderController.
	*
	* @param {APIInterface} api An APIInterface object.
	*/
	constructor(api) {
		this.api = api;
		this.state = {
			slide: {
				loaded:      false,
				locked:      false,
				owned:       false,
				collaborate: false,
				filelimit:   false,
				uploading:   false
			}
		}
	}

	/**
	* Open the asset uploader for a slide.
	*
	* @param {Slide} slide The Slide object to open the uploader for.
	*/
	open(slide) {
		this.slide = slide;
		Object.assign(this.state.slide, {
			loaded:      true,
			locked:      this.slide.is_locked_from_here(),
			owned:       this.slide.is_owned_by_me(),
			collaborate: this.slide.can_collaborate()
		});
		this.update_file_limit_state();
		this.state.slide.loaded = true;
	}

	/**
	* Close the asset uploader.
	*/
	close() {
		this.slide = null;
		Object.assign(this.state.slide, {
			loaded:      false,
			locked:      false,
			owned:       false,
			collaborate: false,
			filelimit:   false,
			uploading:   false
		})
	}

	/**
	* Get the assets of the current slide.
	*
	* @return {SlideAsset[]} The list of assets.
	*/
	get_assets() {
		return this.slide.get('assets');
	}

	/**
	* Remove an asset.
	*
	* @param {string} name The name of the asset to remove.
	*
	* @throws {AssertionError} If no name is supplied.
	*/
	async remove_asset(name) {
		Assert.ok(name != null && name.length != 0, "Empty slide name.");
		await this.slide.remove_asset(name);
		this.update_file_limit_state();
	}

	/**
	* Upload assets to a Slide.
	*
	* This function sets the 'uploading' state value.
	*
	* @param {FileList} files The FileList from an HTML <input type="file">
	*                         element.
	*
	* @throws {AssertionError} If no files are selected.
	* @throws {AssertionError} If no slide is loaded.
	*/
	async upload_assets(files) {
		Assert.ok(files.length != 0, "Empty files list.");
		Assert.ok(this.slide, "No slide loaded.");

		this.state.slide.uploading = true;
		try {
			await this.slide.upload_assets(files);
		} catch (e) {
			this.state.slide.uploading = false;
			throw e;
		}
		this.state.slide.uploading = false;
		this.update_file_limit_state();
	}

	/**
	* Update the slide data.
	*/
	async update() {
		await this.slide.fetch();
	}

	/**
	* Update the 'filelimit' state value with the current data.
	*/
	update_file_limit_state() {
		this.state.slide.filelimit =
			Object.keys(this.slide.get('assets')).length
				>= this.api.limits.SLIDE_MAX_ASSETS;
	}

	/**
	* Get the valid file mime types for input validation.
	*/
	get_valid_file_mime_types() {
		let ret = [];
		for (let m of this.api.limits.SLIDE_ASSET_VALID_MIMES) {
			ret[m.split('/')[1]] = m;
		}
		return ret;
	}

	/**
	* Get the valid filename regex for input validation.
	*
	* TODO: This could be fetched from the server.
	*
	* @return {object} A regex.
	*/
	get_valid_filename_regex() {
		return /^[ A-Za-z0-9_.-]*$/;
	}

	/**
	* Get the maximum filename length for input validation.
	*
	* @return {number} The maximum filename length.
	*/
	get_max_filename_len() {
		return this.api.limits.SLIDE_ASSET_NAME_MAX_LEN;
	}

	/**
	* Get the current slide.
	*
	* @return {Slide} The Slide object.
	*/
	get_slide() {
		return this.slide;
	}

	/**
	* Get all state variables as an object.
	*
	* @return {object} The state variables.
	*/
	get_state() {
		return this.state;
	}
}
module.exports = AssetUploaderController;
