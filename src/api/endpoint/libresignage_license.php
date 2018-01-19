<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
	echo file_get_contents(realpath(LIBRESIGNAGE_ROOT.'/'.LIBRESIGNAGE_LICENSE_FILE_PATH));
