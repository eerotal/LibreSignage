<?php
/*
*  Video thumbnail generator handler.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/thumbnail/common.php');

function gen_vid_thumb(
	string $src,
	string $dest,
	int $wmax,
	int $hmax
) {
	/*
	*  Generate a video thumbnail that fits in a rectangle of size
	*  $wmax x $hmax. This thumbnail generator function can be
	*  enabled with the ENABLE_FFMPEG_THUMBS constant in config.php.
	*  The FFMPEG_PATH and FFPROBE_PATH constants should contain paths
	*  to the ffmpeg and ffprobe binaries respectively. Returns TRUE
	*  on success and FALSE if ffmpeg thumbnail generation is disabled.
	*/

	$raw = [];
	$ret = 0;

	if (ENABLE_FFMPEG_THUMBS !== TRUE) { return FALSE; }
	if (!is_file(FFMPEG_PATH)) {
		throw new ConfigException(
			"Invalid ffmpeg binary path. (".FFMPEG_PATH.")"
		);
	}
	if (!is_file(FFPROBE_PATH)) {
		throw new ConfigException(
			"Invalid ffprobe binary path. (".FFPROBE_PATH.")"
		);
	}

	exec(
		FFPROBE_PATH." ".
			"-v quiet ".
			"-select_streams v:0 ".
			"-show_entries stream=width,height ".
			"-of json ".
			"$src",
		$raw,
		$ret
	);

	if ($ret !== 0) { throw new IntException('ffprobe failed.'); }
	$data = json_decode(implode('', $raw), $assoc = TRUE);
	if (
		$data === NULL
		&& json_last_error() !== JSON_ERROR_NONE
	) { throw new IntException('Failed to parse JSON from ffprobe.'); }

	$dim = get_thumbnail_resolution(
		$data['streams'][0]['width'],
		$data['streams'][0]['height'],
		$wmax,
		$hmax
	);

	exec(
		FFMPEG_PATH." ".
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
	if ($ret !== 0) { throw new IntException('ffmpeg failed.'); }
	return TRUE;
}
