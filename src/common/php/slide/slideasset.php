<?php

/*
*  SlideAsset class declaration used in the Slide class. This class
*  is used for handling uploaded slide assets.
*/

require_once(LIBRESIGNAGE_ROOT.'/common/php/config.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/exportable/exportable.php');
require_once(LIBRESIGNAGE_ROOT.'/common/php/thumbnail/thumbnail.php');

const ASSET_FILENAME_REGEX = '/^[ A-Za-z0-9_.-]*$/';

class SlideAsset extends Exportable {
	static $PRIVATE = [
		'mime',
		'filename',
		'uid',
		'intname',
		'fullpath',
		'thumbname',
		'thumbpath',
		'has_thumb'
	];
	static $PUBLIC = [
		'mime',
		'filename',
		'has_thumb'
	];

	private $mime = NULL;
	private $filename = NULL;
	private $uid = NULL;

	private $intname = NULL;
	private $fullpath = NULL;

	private $thumbname = NULL;
	private $thumbpath = NULL;

	private $has_thumb = FALSE;

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function set_paths(
		string $uid,
		string $ext,
		string $asset_path
	) {
		$this->intname = "{$uid}.{$ext}";
		$this->fullpath = "{$asset_path}/{$this->intname}";
	}

	public function set_thumb_paths(
		string $uid,
		string $asset_path
	) {
		$this->thumbname = "{$uid}_thumb".THUMB_EXT;
		$this->thumbpath = "{$asset_path}/{$this->thumbname}";
	}

	public function gen_uid() {
		$this->uid = get_uid();
	}

	public function new(array $file, Slide $slide) {
		/*
		*  Create a new asset instance and move the uploaded
		*  asset to the asset store of $slide. $file is the
		*  upload data for a single file from $_FILES.
		*/
		assert(!empty($file));
		assert(!empty($slide->get_asset_path()));

		if (strlen($file['name']) > gtlim('SLIDE_ASSET_NAME_MAX_LEN')) {
			throw new ArgException("Asset filename too long.");
		}

		if (!preg_match(ASSET_FILENAME_REGEX, $file['name'])) {
			throw new ArgException(
				'Asset filename contains invalid characters.'
			);
		}

		$mime = mime_content_type($file['tmp_name']);
		if (!in_array($mime, gtlim('SLIDE_ASSET_VALID_MIMES'), TRUE)) {
			throw new FileTypeException("Invalid asset MIME type.");
		}

		$this->filename = basename($file['name']);
		$this->mime = $mime;

		$this->gen_uid();
		$ext = explode('/', $this->mime)[1];
		$this->set_paths($this->uid, $ext, $slide->get_asset_path());

		if (!move_uploaded_file($file['tmp_name'], $this->fullpath)) {
			$this->reset();
			throw new IntException("Failed to store uploaded asset.");
		}

		// Generate a thumbnail for the asset.
		$this->set_thumb_paths($this->uid, $slide->get_asset_path());
		if (!generate_thumbnail(
			$this->fullpath,
			$this->thumbpath,
			THUMB_MAXW,
			THUMB_MAXH
		)) {
			$this->thumbname = NULL;
			$this->thumbpath = NULL;
			$this->has_thumb = FALSE;
		} else {
			$this->has_thumb = TRUE;
		}
	}

	public function remove() {
		if (
			!empty($this->fullpath)
			&& is_file($this->fullpath)
		) {
			unlink($this->fullpath);
		}
		if (
			!empty($this->thumbpath)
			&& is_file($this->thumbpath)
		) {
			unlink($this->thumbpath);
		}
		$this->reset();
	}

	private function reset() {
		foreach ($this::$PRIVATE as $n) { $this->{$n} = NULL; }
	}

	public function get_filename()  { return $this->filename;  }
	public function get_fullpath()  { return $this->fullpath;  }
	public function get_thumbname() { return $this->thumbname; }
	public function get_thumbpath() { return $this->thumbpath; }
	public function get_mime()      { return $this->mime;      }
	public function get_uid()       { return $this->uid;       }
	public function has_thumb()     { return $this->has_thumb; }

	public function clone(Slide $slide): SlideAsset {
		/*
		*  Clone this SlideAsset into the slide $slide. This
		*  function returns the new SlideAsset instance.
		*/
		$asset = clone $this;

		$asset->gen_uid();		
		$asset->set_paths(
			$asset->get_uid(),
			explode('/', $asset->get_mime())[1],
			$slide->get_asset_path()
		);
		copy($this->get_fullpath(), $asset->get_fullpath());

		// Copy thumbnail if it exists.
		if ($this->has_thumb()) {
			$asset->set_thumb_paths($asset->get_uid(), $slide->get_asset_path());
			copy($this->get_thumbpath(), $asset->get_thumbpath());
		}

		return $asset;
	}
}
