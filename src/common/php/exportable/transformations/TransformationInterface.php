<?php

namespace libresignage\common\php\exportable\transformations;

/**
* An interface for defining transformations for Exportable data.
*
* Each class that implements TransformationInterface and is located in
* src/common/php/exportable/transformations is used as a transformation
* when migrating data between different versions of LibreSignage.
*/
interface TransformationInterface {
	/**
	* Return the classname this transformation applies to.
	*
	* @return string The fully-qualified classname as a string.
	*/
	public static function classname(): string;

	/**
	* Return the version of data from which this transformation can transform.
	*
	* @return string The version string.
	*/
	public static function from_version(): string;

	/**
	* Return the version of data to which this transformation can transform.
	*
	* @return string The version string.
	*/
	public static function to_version(): string;

	/**
	* Perform the transformation of data.
	*
	* @param array The data to be transformed. This function modifies
	*              the array passed as the argument.
	*/
	public static function transform(array &$data);
}
