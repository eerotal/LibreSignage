<?php
	/*
	*  LibreSignage utility functions.
	*/

	function array_is_equal(array $a, array $b) {
		/*
		*  Check if array $a has the same values
		*  as array $b. Returns TRUE if $a is equal
		*  to $b and FALSE otherwise.
		*/
		if (array_is_subset($a, $b) &&
			count($a) == count($b)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function array_is_subset(array $a, array $b) {
		/*
		*  Check if array $a is a subset of array
		*  $b. Returns true if $a is a subset of
		*  $b and false otherwise.
		*/
		if (count(array_intersect($a, $b)) == count($a)) {
			return true;
		} else {
			return false;
		}
	}
