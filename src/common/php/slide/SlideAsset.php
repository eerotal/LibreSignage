<?php

namespace common\php\slide;

use \common\php\Util;
use \common\php\Config;
use \common\php\Exportable;
use \common\php\slide\Slide;
use \common\php\thumbnail\Thumbnail;

/**
* A class for handling file uploads to slides.
*/
final class SlideAsset extends Exportable {
	static $PRIVATE = [
		'filename',
		'mime',
		'uid',
		'slide_id',
		'has_thumb'
	];
	static $PUBLIC = [
		'mime',
		'filename',
		'has_thumb'
	];

	const FILENAME_REGEX = '/^[ A-Za-z0-9_.-]*$/';

	private $filename = NULL;
	private $mime = NULL;
	private $uid = NULL;
	private $slide_id = NULL;

	private $has_thumb = FALSE;

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	/**
	* Create a new asset instance and move the uploaded asset to
	* the asset store of $slide.
	*
	* @param array $file        The upload data for a single file from $_FILES.
	* @param Slide $slide       The slide to move the asset to.
	*
	* @throws ArgException      if the asset filename is longer
	*                           than SLIDE_ASSET_NAME_MAX_LEN
	* @throws ArgException      if the asset name contains invalid chars.
	* @throws FileTypeException if the asset MIME type is not allowed.
	* @throws IntException      if move_uploaded_file() fails.
	*/
	public function new(array $file, Slide $slide) {
		assert(
			!empty($file),
			new ArgException('$file cannot be empty')
		);

		if (strlen($file['name']) > Config::limit('SLIDE_ASSET_NAME_MAX_LEN')) {
			throw new ArgException('Asset filename too long.');
		}

		if (!preg_match(SlideAsset::FILENAME_REGEX, $file['name'])) {
			throw new ArgException('Asset name contains invalid characters.');
		}

		$this->slide_id = $slide->get_id();
		$this->gen_uid();

		$this->filename = basename($file['name']);
		$this->mime = mime_content_type($file['tmp_name']);

		if (!in_array($this->mime, Config::limit('SLIDE_ASSET_VALID_MIMES'), TRUE)) {
			throw new FileTypeException('Invalid asset MIME type.');
		}
		if (!move_uploaded_file($file['tmp_name'], $this->get_internal_path())) {
			throw new IntException('Failed to store uploaded asset.');
		}

		$this->has_thumb = TRUE;
		try {
			Thumbnail::create(
				$this->get_internal_path(),
				$this->get_internal_thumb_path(),
				Config::config('THUMB_MAXW'),
				Config::config('THUMB_MAXH')
			);
		} catch (Exception $e) { $this->has_thumb = FALSE; }
	}

	/**
	* Remove the loaded asset.
	*
	* @throws IntException if removing the asset fails.
	* @throws IntException if removing the thumbnail fails.
	*/
	public function remove() {
		$this->assert_ready();

		if (is_file($this->get_internal_path())) {
			if (!unlink($this->get_internal_path())) {
				throw new IntException('Failed to remove asset.');
			}
		}
		if (is_file($this->get_internal_thumb_path())) {
			if (!unlink($this->get_internal_thumb_path())) {
				throw new IntException('Failed to remove asset thumbnail.');
			}
		}
	}

	/**
	* Assert that a SlideAsset is ready.
	*
	* @throws IntException if the SlideAsset is not ready.
	*/
	private function assert_ready() {
		assert(
			!empty($this->filename)
			&& !empty($this->uid)
			&& !empty($this->mime)
			&& !empty($this->slide_id),
			new IntException('SlideAsset not ready.')
		);
	}

	/**
	* Generate a new UID for an asset.
	*/
	public function gen_uid() {
		$this->uid = get_uid();
	}

	/**
	* Get the internal filename of an asset.
	*
	* @return string The internal asset filename.
	*/
	public function get_internal_name(): string {
		return $this->uid.explode('/', $this->mime)[1];
	}

	/**
	* Get the internal path of an asset.
	*
	* @return string The internal asset path.
	*/
	public function get_internal_path(): string {
		$s = new Slide();
		$s->load($this->slide_id);
		return $s->get_asset_path().'/'.$this->get_internal_name();
	}

	/**
	* Get the internal filename of a thumbnail.
	*
	* @return string The internal thumbnail filename.
	*/
	public function get_internal_thumb_name(): string {
		return $this->uid.'_thumb'.Config::config('THUMB_EXT');
	}

	/**
	* Get the internal path of a thumbnail.
	*
	* @return string The internal thumbnail path.
	*/
	public function get_internal_thumb_path(): string {
		$s = new Slide();
		$s->load($this->slide_id);
		return $s->get_asset_path().'/'.$this->get_internal_thumb_name();
	}

	public function get_filename()  { return $this->filename;  }
	public function get_mime()      { return $this->mime;      }
	public function get_uid()       { return $this->uid;       }
	public function has_thumb()     { return $this->has_thumb; }

	/**
	* Clone this SlideAsset into the slide $slide.
	*
	* @param Slide $slide The slide to clone the asset into.
	*
	* @return SlideAsset The cloned SlideAsset instance.
	*
	* @throws IntException if copying the asset fails.
	* @throws IntException if copying the asset thumbnail fails.
	*/
	public function clone(Slide $slide): SlideAsset {
		$this->assert_ready();

		$asset = clone $this;
		$asset->gen_uid();		

		if (!copy($this->get_fullpath(), $asset->get_fullpath())) {
			throw new IntException('Failed to copy asset.');
		}

		if ($this->has_thumb()) {
			if (!copy($this->get_thumbpath(), $asset->get_thumbpath())) {
				throw new IntException('Failed to copy asset thumbnail.');	
			}
		}
		return $asset;
	}
}
