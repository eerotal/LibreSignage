<?php

namespace libresignage\common\php\thumbnail;

use libresignage\common\php\Config;
use libresignage\common\php\exceptions\IntException;
use libresignage\common\php\JSONUtils;
use libresignage\common\php\thumbnail\ThumbnailUtil;
use libresignage\common\php\thumbnail\ThumbnailGeneratorException;
use libresignage\common\php\thumbnail\ThumbnailGeneratorInterface;

/**
* Thumbnail generator for video files.
*
* * This generator can be enabled with the ENABLE_FFMPEG_THUMBS config value.
* * You must also set the FFMPEG_PATH and FFPROBE_PATH config values.
*/
final class VidThumbnailGenerator implements ThumbnailGeneratorInterface {
	/**
	* @see ThumbnailGeneratorInterface::provides_import()
	*/
	public static function provides_import(): array {
		return ['video/mp4', 'video/ogg', 'video/webm'];
	}

	/**
	* @see ThumbnailGeneratorInterface::provides_export()
	*/
	public static function provides_export(): array {
		return ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
	}

	/**
	* @see ThumbnailGeneratorInterface::is_enabled()
	*/
	public static function is_enabled(): bool {
		return Config::config('ENABLE_FFMPEG_THUMBS');
	}

	/**
	* @see ThumbnailGeneratorInterface::ensure_prerequisites_met()
	*/
	public static function ensure_prerequisites_met() {
		if (!is_file(Config::config('FFMPEG_PATH'))) {
			throw new ThumbnailGeneratorException(
				"Invalid ffmpeg binary path. (".
				Config::config('FFMPEG_PATH').")"
			);
		}

		if (!is_file(Config::config('FFPROBE_PATH'))) {
			throw new ThumbnailGeneratorException(
				"Invalid ffprobe binary path. (".
				Config::config('FFPROBE_PATH').")"
			);
		}

		$tmp = preg_replace('/\s/', '', ini_get('disable_functions'));
		if (in_array('exec', explode(',', $tmp))) {
			throw new ThumbnailGeneratorException(
				"PHP exec() required for video thumbnail generation."
			);
		}
	}

	/**
	* @see ThumbnailGeneratorInterace::create()
	*/
	public static function create(
		string $src,
		string $dest,
		int $wmax,
		int $hmax
	) {
		$raw = [];
		$ret = 0;

		if (!self::is_enabled()) { return; }

		exec(
			Config::config('FFPROBE_PATH')." ".
				"-v quiet ".
				"-select_streams v:0 ".
				"-show_entries stream=width,height ".
				"-of json ".
				"$src",
			$raw,
			$ret
		);

		if ($ret !== 0) { throw new IntException("ffprobe failed ($ret)."); }
		$data = JSONUtils::decode(implode('', $raw), $assoc=TRUE);

		$dim = get_thumbnail_resolution(
			$data['streams'][0]['width'],
			$data['streams'][0]['height'],
			$wmax,
			$hmax
		);

		exec(
			Config::config('FFMPEG_PATH')." ".
				"-v quiet ".
				"-y ".
				"-ss 00:00:10 ".
				"-t 1 ".
				"-i '$src' ".
				"-r 1 ".
				"-s {$dim['width']}x{$dim['height']} ".
				"-frames:v:0 1 ".
				"$dest",
			$raw,
			$ret
		);
		if ($ret !== 0) { throw new IntException("ffmpeg failed ($ret)."); }
	}
}
