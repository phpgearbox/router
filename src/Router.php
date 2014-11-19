<?php namespace Gears;
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
use Gears\Di\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router as LaravelRouter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router extends Container
{
	/**
	 * Property: routesPath
	 * =========================================================================
	 * This is the file path to where our route file or files are stored.
	 */
	protected $injectRoutesPath;

	/**
	 * Property: notFound
	 * =========================================================================
	 * If this is set we will use this as the 404 response.
	 * If nothing is supplied we output a nice looking default 404 page.
	 * Credits: http://html5boilerplate.com/
	 */
	protected $injectNotFound;

	/**
	 * Property: exitOnComplete
	 * =========================================================================
	 * When set to true (the default) we will exit the current PHP process after
	 * sending the response. This ensures that no other output can mess things
	 * up. However some setups may require the opposite.
	 */
	protected $injectExitOnComplete;

	/**
	 * Property: dispatcher
	 * =========================================================================
	 * An instance of ```Illuminate\Events\Dispatcher```.
	 */
	protected $injectDispatcher;

	/**
	 * Property: finder
	 * =========================================================================
	 * An instance of ```Symfony\Component\Finder\Finder```.
	 */
	protected $injectFinder;

	/**
	 * Property: request
	 * =========================================================================
	 * An instance of ```Illuminate\Http\Request```.
	 */
	protected $injectRequest;

	/**
	 * Property: laravelRouter
	 * =========================================================================
	 * An instance of ```Illuminate\Routing\Router```.
	 */
	protected $injectLaravelRouter;

	/**
	 * Property: router
	 * =========================================================================
	 * This is where we statically store a copy of the LaravelRouter
	 * after the container has been installed.
	 */
	private static $router;

	/**
	 * Method: setDefaults
	 * =========================================================================
	 * This is where we set all our defaults. If you need to customise this
	 * container this is a good place to look to see what can be configured
	 * and how to configure it.
	 * 
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 * 
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	protected function setDefaults()
	{
		$this->exitOnComplete = true;

		$this->dispatcher = function()
		{
			return new Dispatcher;
		};

		$this->finder = $this->factory(function()
		{
			return new Finder;
		});

		$this->request = function()
		{
			return Request::createFromGlobals();
		};

		$this->laravelRouter = function()
		{
			return new LaravelRouter($this->dispatcher);
		};
	}

	/**
	 * Method: install
	 * =========================================================================
	 * Once the router has been configured, simply run this method and we will
	 * resolve the router from the container. Setup some class alias's,
	 * add the routes and finally dispatch the underlying router.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function install()
	{
		// Resolve the router from the container
		self::$router = $this->laravelRouter;

		// Alias ourselves
		if (!class_exists('\Route'))
		{
			class_alias('\Gears\Router', '\Route');
		}

		if (!class_exists('\Gears\Route'))
		{
			class_alias('\Gears\Router', '\Gears\Route');
		}

		// Check if the path is a file or dir
		if (is_dir($this->routesPath))
		{
			// Loop through all our route files.
			foreach ($this->finder->files()->in($this->routesPath) as $file)
			{
				// Load the file
				require($file->getRealpath());
			}
		}
		else
		{
			// Load the single file
			require($this->routesPath);
		}

		/*
		 * Now that we have looped through all our routes we can reset the
		 * static router property. We do this so that further static calls
		 * to this class will fail.
		 * 
		 * We want to avoid this situation:
		 * 
		 * ```php
		 * $router1 = new Gears\Router();
		 * $router1->install();
		 * 
		 * $router2 = new Gears\Router();
		 * 
		 * // this route would not be added to router2 but to router1
		 * Route::get('/', function(){ return 'foo'; });
		 * ```
		 */
		$router = self::$router;
		self::$router = null;

		try
		{
			// Run the router
			$response = $router->dispatch($this->request);

			// Send the response
			$response->send();
		}
		catch (NotFoundHttpException $e)
		{
			/*
			 * If the 404 is explicitly set to the boolean value of false.
			 * We re-throw the exception and make that the responsibility
			 * of the caller.
			 */
			if ($this->notFound === false) throw $e;

			// Output the 404 header
			header('HTTP/1.0 404 Not Found');

			// Output our 404 page
			if (!is_null($this->notFound))
			{
				echo $this->notFound;
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
		if ($this->exitOnComplete) exit;
	}

	/**
	 * Method: dispatch
	 * =========================================================================
	 * This is just an alias of install as it makes slightly more sense to
	 * dispatch the router than to install it.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function dispatch()
	{
		$this->install();
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
	 * - $name: The name of the method to call.
	 * - $args: The argumnent array that is given to us.
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * mixed
	 * 
	 * Throws:
	 * -------------------------------------------------------------------------
	 * - RuntimeException: When the router has not been installed.
	 */
	public static function __callStatic($name, $args)
	{
		if (empty(self::$router))
		{
			throw new RuntimeException('You need to install a router first!');
		}

		return call_user_func_array([self::$router, $name], $args);
	}
}