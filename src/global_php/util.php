<?php
	function array_is_subset(array $a, array $b) {
		/*
		*  Check if the array 'a' is a subset of the
		*  array 'b'. Returns true if 'a' is a subset
		*  of 'b' and false otherwise.
		*/
		if (count(array_intersect($a, $b)) == count($a)) {
			return true;
		} else {
			return false;
		}
	}
