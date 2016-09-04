<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

trait TGetter
{
	/**
	 * Allow setting fooBar getter with getFooBar().
	 *
	 * @param  string $attribute
	 *
	 * @return mixed
	 */
	public function __get( $attribute )
	{
		if( is_callable([static::class, $getter= 'get'.implode('',array_map('ucfirst',explode('_',$attribute))),]) ){
			return static::$getter();
		}else{
			throw new \Exception(static::class.' has no attribute named '.$attribute);
		}
	}
}
