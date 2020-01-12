<?php

namespace libresignage\common\php\slide;

use libresignage\common\php\Util;
use libresignage\common\php\Config;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\slide\Slide;
use libresignage\common\php\thumbnail\Thumbnail;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
* A class for handling file uploads to slides.
*/
final class SlideAsset extends Exportable {
	const FILENAME_REGEX = '/^[ A-Za-z0-9_.-]*$/';
	const THUMB_SUFFIX   = '_thumb';

	private $filename = NULL;
	private $mime = NULL;
	private $uid = NULL;
	private $slide_id = NULL;
	private $has_thumb = FALSE;
	private $hash = NULL;

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_write() {}

	public static function __exportable_private(): array {
		return [
			'filename',
			'mime',
			'uid',
			'slide_id',
			'has_thumb',
			'hash'
		];
	}

	public static function __exportable_public(): array {
		return [
			'mime',
			'filename',
			'has_thumb',
			'hash'
		];
	}

	/**
	* Validate an UploadedFile.
	*
	* @throws ArgException if the original filename is too long.
	* @throws ArgException if the original filename contains invalid chars.
	* @throws ArgException if the mime type of the file is invalid.
	*/
	public static function validate_file(UploadedFile $file) {
		$name = $file->getClientOriginalName();
		if (strlen($name) > Config::limit('SLIDE_ASSET_NAME_MAX_LEN')) {
			throw new ArgException('Filename too long.');
		}
		if (!preg_match(SlideAsset::FILENAME_REGEX, $name)) {
			throw new ArgException('Filename contains invalid characters.');
		}
		if (
			!in_array(
				$file->getMimeType(),
				Config::limit('SLIDE_ASSET_VALID_MIMES'),
				TRUE
			)
		) { throw new FileTypeException('Invalid file MIME type.'); }
	}

	/**
	* Create a new asset instance and move the uploaded asset to
	* the asset store of $slide.
	*
	* @see SlideAsset::validate_file() for validation exceptions.
	*
	* @param UploadedFile $file The upload data for a single file.
	* @param Slide $slide       The slide to move the asset to.
	*
	* @throws IntException      if moving the file fails.
	*/
	public function new(UploadedFile $file, Slide $slide) {
		self::validate_file($file);

		$this->slide_id = $slide->get_id();
		$this->mime = $file->getMimeType();
		$this->filename = $file->getClientOriginalName();
		$this->gen_uid();

		try {
			$file->move(
				$this->get_internal_dir(),
				$this->get_internal_name()
			);
		} catch (FileException $e) {
			throw new IntException('Failed to store uploaded asset.');
		}

		$this->hash();

		$this->has_thumb = TRUE;
		try {
			$thumb = new Thumbnail();
			$thumb->create(
				$this->get_internal_path(),
				$this->get_internal_thumb_dir(),
				self::THUMB_SUFFIX,
				Config::config('THUMB_MIME'),
				Config::config('THUMB_MAXW'),
				Config::config('THUMB_MAXH')
			);
		} catch (\Exception $e) { $this->has_thumb = FALSE; }
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
		$this->uid = Util::get_uid();
	}

	/**
	* Calculate and store the MD5 hash an asset.
	*/
	public function hash() {
		$this->hash = md5_file($this->get_internal_path());
	}

	/**
	* Get the internal filename of an asset.
	*
	* @return string The internal asset filename.
	*/
	public function get_internal_name(): string {
		return $this->uid.'.'.explode('/', $this->mime)[1];
	}

	/**
	* Get the internal directory path of an asset.
	*
	* @return string The internal directory path.
	*/
	public function get_internal_dir(): string {
		return Slide::get_asset_path($this->slide_id);
	}

	/**
	* Get the internal path of an asset.
	*
	* @return string The internal asset path.
	*/
	public function get_internal_path(): string {
		return $this->get_internal_dir().'/'.$this->get_internal_name();
	}

	/**
	* Get the internal filename of a thumbnail.
	*
	* @return string The internal thumbnail filename.
	*/
	public function get_internal_thumb_name(): string {
		return $this->uid.
			self::THUMB_SUFFIX.
			'.'.
			explode('/', Config::config('THUMB_MIME'))[1];
	}

	/**
	* Get the internal directory path for thumbnails.
	*
	* @return string The thumbnail directory path.
	*/
	public function get_internal_thumb_dir(): string {
		return Slide::get_asset_path($this->slide_id);
	}

	/**
	* Get the internal path of a thumbnail.
	*
	* @return string The internal thumbnail path.
	*/
	public function get_internal_thumb_path(): string {
		return $this->get_internal_thumb_dir().'/'.$this->get_internal_thumb_name();
	}

	public function get_filename()  { return $this->filename;  }
	public function get_mime()      { return $this->mime;      }
	public function get_uid()       { return $this->uid;       }
	public function has_thumb()     { return $this->has_thumb; }
	public function get_hash()      { return $this->hash;      }

	/**
	* Get the last modified time of an asset.
	*
	* @return int The asset modification Unix timestamp.
	*/
	public function get_mtime(): int {
		return filemtime($this->get_internal_path());
	}

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

		if (
			!copy(
				$this->get_internal_path(),
				$asset->get_internal_path()
			)
		) {
			throw new IntException('Failed to copy asset.');
		}

		if ($this->has_thumb()) {
			if (
				!copy(
					$this->get_internal_thumb_path(),
					$asset->get_internal_thumb_path()
				)
			) {
				throw new IntException('Failed to copy asset thumbnail.');	
			}
		}
		return $asset;
	}
}
