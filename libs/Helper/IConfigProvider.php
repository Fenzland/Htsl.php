<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

interface IConfigProvider
{
	/**
	 * Getting configuration with cascaded keys.
	 *
	 * @access public
	 *
	 * @param  string ...$key
	 *
	 * @return mixed
	 */
	public function getConfig( string...$key );
}
