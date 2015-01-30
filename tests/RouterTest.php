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
	protected $http;
	
	protected function setUp()
	{
		// Get a new guzzle client
		$this->http = GuzzleTester();
	}
	
	public function testDefaultRoute()
	{
		$this->assertEquals('Hello World', $this->http->get()->getBody());
	}
	
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
	
	public function testRoutesWithSameNameButDifferentVerbs()
	{
		$this->assertEquals('FOOBAR GET', $this->http->get('/foobar')->getBody());
		$this->assertEquals('FOOBAR POST', $this->http->post('/foobar')->getBody());
	}
	
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
	
	public function testContainerCalls()
	{
		$this->assertEquals('bar', $this->http->get('/container-calls?foo=bar')->getBody());
	}
	
	public function testRequestInterface()
	{
		$this->assertEquals('bar', $this->http->get('/request-interface?foo=bar')->getBody());
	}
}