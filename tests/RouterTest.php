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

class RouterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Property: $http
	 * =========================================================================
	 * We store an instance of GuzzleHttp\Client here.
	 */
	protected $http;

	/**
	 * Method: setUp
	 * =========================================================================
	 * This is run before our tests. It creates the above properties.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	protected function setUp()
	{
		// Get a new guzzle client
		$this->http = GuzzleTester();
	}

	/**
	 * Method: testDefaultRoute
	 * =========================================================================
	 * Tests to make sure we get the hello world response.
	 * The counter part route script is:
	 * 
	 *     ./tests/environment/routes/index.GET.php
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function testDefaultRoute()
	{
		$this->assertEquals('Hello World', $this->http->get()->getBody());
	}

	/**
	 * Method: test404Response
	 * =========================================================================
	 * This makes sure we get a 404 response back for an invalid route.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function test404Response()
	{
		try
		{
			$this->http->get('/404');
		}
		catch (GuzzleHttp\Exception\ClientException $e)
		{
			return;
		}

		$this->fail('404 Exception Not Thrown');
	}

	/**
	 * Method: testRoutesWithSameNameButDifferentVerbs
	 * =========================================================================
	 * We can have routes of the same name but with different HTTP verbs.
	 * The counter part route script is:
	 * 
	 *     ./tests/environment/routes/foobar.GET.php
	 *     ./tests/environment/routes/foobar.POST.php
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function testRoutesWithSameNameButDifferentVerbs()
	{
		$this->assertEquals('FOOBAR GET', $this->http->get('/foobar')->getBody());
		$this->assertEquals('FOOBAR POST', $this->http->post('/foobar')->getBody());
	}

	/**
	 * Method: testUriVars
	 * =========================================================================
	 * Test variables in the URI segments.
	 * The counter part route script is:
	 * 
	 *     ./tests/environment/routes/uri-vars-{name}-{age}.GET.php
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function testUriVars()
	{
		$name = 'Brad Jones';
		$age = @date('Y') - 1988;

		$this->assertEquals
		(
			'Hello '.$name.' of '.$age.' years old.',
			$this->http->get('/uri/vars/'.$name.'/'.$age)->getBody()
		);
	}
}