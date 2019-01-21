var assert = require('ls-assert').assert;

class AssetUploaderController {
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

	open(slide) {
		/*
		*  Open the asset uploader for the Slide object 'slide'.
		*/
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

	close() {
		/*
		*  Close the assetuploader and reset all variables.
		*/
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

	get_assets() {
		/*
		*  Get the slide assets list.
		*/
		return this.slide.get('assets');
	}

	async remove_asset(name) {
		/*
		*  Remove asset 'name'.
		*/
		assert(name != null && name.length != 0, "Empty slide name.");
		await this.slide.remove_asset(name);
		this.update_file_limit_state();
	}

	async upload_assets(files) {
		/*
		*  Upload the files specified in 'files' which
		*  is an object created by an HTML <input type="file">
		*  object or a similarly structured one. This function
		*  sets the 'uploading' state value.
		*/
		assert(files.length != 0, "Empty files list.");
		assert(this.slide, "No slide loaded.");

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

	async update() {
		/*
		*  Update the slide data.
		*/
		await this.slide.fetch();
	}

	update_file_limit_state() {
		/*
		*  Update the 'filelimit' state value with the current data.
		*/
		this.state.slide.filelimit = 
			this.slide.get('assets').length
				>= this.api.limits.SLIDE_MAX_ASSETS;
	}

	get_valid_file_mime_types() {
		/*
		*  Get the valid file mime types for input validation.
		*/
		let ret = [];
		for (let m of this.api.limits.SLIDE_ASSET_VALID_MIMES) {
			ret[m.split('/')[1]] = m;
		}
		return ret;
	}

	get_valid_filename_regex() {
		/*
		*  Get the valid filename regex for input validation.
		*  TODO: This could be fetched from the server.
		*/
		return /^[ A-Za-z0-9_.-]*$/;
	}

	get_max_filename_len() {
		/*
		*  Get the maximum filename length for input validation.
		*/
		return this.api.limits.SLIDE_ASSET_NAME_MAX_LEN;
	}

	get_slide() {
		return this.slide;
	}

	get_state() {
		return this.state;
	}
}
exports.AssetUploaderController = AssetUploaderController;
