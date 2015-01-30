<?php namespace Gears\Router;
////////////////////////////////////////////////////////////////////////////////
// __________ __             ________                   __________              
// \______   \  |__ ______  /  _____/  ____ _____ ______\______   \ _______  ___
//  |     ___/  |  \\____ \/   \  ____/ __ \\__  \\_  __ \    |  _//  _ \  \/  /
//  |    |   |   Y  \  |_> >    \_\  \  ___/ / __ \|  | \/    |   (  <_> >    < 
//  |____|   |___|  /   __/ \______  /\___  >____  /__|  |______  /\____/__/\_ \
//                \/|__|           \/     \/     \/             \/            \/
// -----------------------------------------------------------------------------
//          Designed and Developed by Brad Jones <brad @="bjc.id.au" />         
// -----------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////

use RuntimeException;
use Illuminate\Http\Request as LaravelRequest;

class Request
{
	private static $instance;
	
	public static function createFromGlobals()
	{
		static::$instance = LaravelRequest::createFromGlobals();
		
		return static::$instance;
	}
	
	public static function __callStatic($name, $args)
	{
		if (empty(static::$instance))
		{
			throw new RuntimeException('You need to install a router first!');
		}
		
		return call_user_func_array([self::$instance, $name], $args);
	}
}