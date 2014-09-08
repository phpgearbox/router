<?php
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

namespace Gears;

use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router as LaravelRouter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router
{
	/**
	 * Property: router
	 * =========================================================================
	 * This is where we store a copy of the actual LaravelRouter.
	 */
	private static $router;

	/**
	 * Method: install
	 * =========================================================================
	 * To setup the router simply call this method, with a path to a single file
	 * or a directory containing route files. ie: One route per file.
	 *
	 * Example usage:
	 *
	 *     Gears\Router::install('/path/to/my/routes');
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $path - A file path to a route file or directory.
	 *
	 * $notFound - If this is set we will use this as the 404 response.
	 * If nothing is supplied we output a nice looking default 404 page.
	 * Credits: http://html5boilerplate.com/
	 * 
	 * $exitOnComplete - When set to true (the default) we will exit the current
	 * PHP process after sending the response. This ensures that no other output
	 * can mess things up. However some setups may require the opposite.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public static function install($path, $notFound = null, $exitOnComplete = true)
	{
		// Create the new laravel router
		self::$router = new LaravelRouter(new Dispatcher);

		// Alias ourselves
		if (!class_exists('\Route'))
		{
			class_alias('\Gears\Router', '\Route');
		}
		else
		{
			class_alias('\Gears\Router', '\Gears\Route');
		}

		// Check if the path is a file or dir
		if (is_dir($path))
		{
			// Loop through all our route files.
			foreach (with(new Finder())->files()->in($path) as $file)
			{
				// Load the file
				require($file->getRealpath());
			}
		}
		else
		{
			// Load the single file
			require($path);
		}

		try
		{
			// Run the router
			$response = self::$router->dispatch(Request::createFromGlobals());

			// Send the response
			$response->send();
		}
		catch (NotFoundHttpException $e)
		{
			// Output our 404 page
			if (!empty($notFound))
			{
				echo $notFound;
			}
			else
			{
				echo
				'
					<!doctype html>
					<html lang="en">
						<head>
							<meta charset="utf-8">
							<title>Page Not Found</title>
							<meta name="viewport" content="width=device-width, initial-scale=1">
							<style>
								* { line-height: 1.2; margin: 0; }
								html { color: #888; display: table; font-family: sans-serif; height: 100%; text-align: center; width: 100%; }
								body { display: table-cell; vertical-align: middle; margin: 2em auto; }
								h1 { color: #555; font-size: 2em; font-weight: 400; }
								p { margin: 0 auto; width: 280px; }
								@media only screen and (max-width: 280px)
								{
									body, p { width: 95%; }
									h1 { font-size: 1.5em; margin: 0 0 0.3em 0; }
								}
							</style>
						</head>
						<body>
							<h1>Page Not Found</h1>
							<p>Sorry, but the page you were trying to view does not exist.</p>
						</body>
					</html>
					<!-- IE needs 512+ bytes: http://blogs.msdn.com/b/ieinternals/archive/2010/08/19/http-error-pages-in-internet-explorer.aspx -->
				';
			}
		}

		// We are all done now
		if ($exitOnComplete) exit;
	}

	/**
	 * Method: __callStatic
	 * =========================================================================
	 * Okay so this is the magic method that makes it possible to do this:
	 *
	 *     Route::get('/', function(){ return 'Hello World!'; });
	 *
	 * For more info on how the laravel route api works, please see:
	 * http://laravel.com/docs/routing
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $name - The name of the method to call.
	 * $args - The argumnent array that is given to us.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * mixed
	 */
	public static function __callStatic($name, $args)
	{
		return call_user_func_array([self::$router, $name], $args);
	}
}