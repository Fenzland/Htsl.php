<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

trait TGetter
{
	public function __get( $attribute )
	{
		if( is_callable([static::class, $getter= 'get'.implode('',array_map('ucfirst',explode('_',$attribute))),]) ){
			return static::$getter();
		}else{
			throw new \Exception(static::class.' has no attribute named '.$attribute);
		}
	}
}
