<?php

use PHPUnit_Framework_Assert as PHPUnit;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

	protected function initDB()
	{
		Artisan::call('migrate');
	}

	protected function resetDB()
	{
	}

	function getCaller() {
		$trace = debug_backtrace();
		$name = $trace[2]['function'];
		return empty($name) ? 'global' : $name;
	}

    public function assertRedirect($uri, $str = null)
    {
        PHPUnit::assertInstanceOf('Illuminate\Http\RedirectResponse', $this->response);


		if($str)
			PHPUnit::assertEquals($this->app['url']->to($uri), $this->response->headers->get('Location'),'Caller: '.$this->getCaller().' '.$str);
		else
			PHPUnit::assertEquals($this->app['url']->to($uri), $this->response->headers->get('Location'));
    }

}
