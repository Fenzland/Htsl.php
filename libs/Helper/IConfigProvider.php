<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

interface IConfigProvider
{
	public function getConfig( string...$key );
}
