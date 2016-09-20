<?php

namespace Htsl\Helper;

/**
 * Change 'abc_def-ghi jkh' to 'abcDefGhiJkh'.
 *
 * @param  string $input
 *
 * @return string
 */
function camel_case( string$input ):string
{
	return lcfirst(studly_case($input));
}

/**
 * Change 'abc_def-ghi jkh' to 'AbcDefGhiJkh'.
 *
 * @param  string $input
 *
 * @return string
 */
function studly_case( string$input ):string
{
	return str_replace(' ','',ucwords(str_replace(['-', '_'], ' ', $input)));
}

/**
 * Change 'AbcDEFGhiJkh' or 'abcDefGhiJkh' to 'abc_def_ghi_jkh'.
 *
 * @param  string $input
 *
 * @return string
 */
function snake_case( string$input ):string
{
	return strtolower(preg_replace('/(?<=[^A-Z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][^A-Z])/','_',$input));
}
