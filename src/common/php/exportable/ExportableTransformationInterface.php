<?php

namespace libresignage\common\php\exportable;

/**
* An interface for defining transformations for Exportable data.
*
* Each class that implements ExportableTransformation and is located in
* src/common/php/exportable/transformations is used as a transformation
* when migrating data between different versions of LibreSignage.
*/
interface ExportableTransformationInterface {
	/**
	* Return the classname this ExportableTransformation applies to.
	*
	* @return string The fully-qualified classname as a string.	
	*/
	public function classname(): string;

	/**
	* Return the version of data this ExportableTransformation can transform.
	*
	* @return array The LibreSignage version this ExportableTransformation
	*               applies to. The version string is specified as an array
	*               of the individual version number components, ie.
	*               1.1.5 = ['1', '1', '5']. You can specify an asterisk (*)
	*               to match any component, for example ['1', '1', '*'].
	*/
	public function version(): string;
	
	/**
	* Perform the transformation of data.
	*
	* @param array The input data.
	*
	* @return array The transformed data.
	*/
	public function transform($data);
}
