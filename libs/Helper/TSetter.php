<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

trait TSetter
{
	public function __set( $attribute, $value )
	{
		if( is_callable([static::class, $setter= 'set'.implode('',array_map('ucfirst',explode('_',$attribute))),]) ){
			return static::$setter($value);
		}else{
			throw new \Exception(static::class.' has no attribute named '.$attribute);
		}
	}
}
