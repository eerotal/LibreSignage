<?php

/*
*  SlideAsset class declaration used in the Slide class. This class
*  is used for handling uploaded slide assets.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/exportable/exportable.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/thumbnail.php');

const ASSET_MIMES = [
	'image/png',
	'image/jpeg',
	'image/gif',
	'video/mp4',
	'video/webm',
	'video/ogg'
];

class SlideAsset extends Exportable {
	static $PRIVATE = [
		'mime',
		'filename',
		'uid',
		'intname',
		'fullpath',
		'thumbname',
		'thumbpath'
	];
	static $PUBLIC = [
		'mime',
		'filename'
	];

	private $mime = NULL;
	private $filename = NULL;
	private $uid = NULL;

	private $intname = NULL;
	private $fullpath = NULL;

	private $thumbname = NULL;
	private $thumbpath = NULL;

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function new(array $file, string $asset_path) {
		/*
		*  Create a new asset instance and move the uploaded
		*  asset to $asset_path. $file is the upload data for
		*  a single file from $_FILES.
		*/
		assert(!empty($file));
		assert(!empty($asset_path));

		$mime = mime_content_type($file['tmp_name']);
		if (!in_array($mime, ASSET_MIMES, TRUE)) {
			throw new FileTypeException("Invalid asset MIME type.");
		}
		if (strlen($file['name']) > gtlim('SLIDE_ASSET_NAME_MAX_LEN')) {
			throw new ArgException("Asset filename too long.");
		}

		$this->filename = basename($file['name']);
		$this->mime = $mime;
		$this->uid = get_uid();

		$ext = explode('/', $this->mime)[1];

		$this->intname = "{$this->uid}.{$ext}";
		$this->fullpath = "{$asset_path}/{$this->intname}";

		if (!move_uploaded_file($file['tmp_name'], $this->fullpath)) {
			$this->reset();
			throw new IntException("Failed to store uploaded asset.");
		}

		// Generate a thumbnail for the asset.
		$this->thumbname = "{$this->uid}_thumb.{$ext}";
		$this->thumbpath = "{$asset_path}/{$this->thumbname}";

		if (!generate_thumbnail(
			$this->fullpath,
			$this->thumbpath,
			THUMB_MAXW,
			THUMB_MAXH
		)) {
			$this->thumbname = NULL;
			$this->thumbpath = NULL;
		}
	}

	public function remove() {
		if (!empty($this->fullpath)) {
			unlink($this->fullpath);
			unlink($this->thumbpath);
			$this->reset();
		}
	}

	private function reset() {
		foreach ($this::$PRIVATE as $n) { $this->{$n} = NULL; }
	}

	public function get_filename() { return $this->filename; }
	public function get_fullpath() { return $this->fullpath; }
	public function get_thumbname() { return $this->thumbname; }
	public function get_thumbpath() { return $this->thumbpath; }
	public function get_mime() { return $this->mime; }
}
