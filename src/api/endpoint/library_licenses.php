<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	echo file_get_contents(realpath(LIBRESIGNAGE_ROOT.'/'.LIBRARY_LICENSES_FILE_PATH));
