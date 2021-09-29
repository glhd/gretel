<?php

namespace Glhd\Gretel\Tests;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Exceptions\UnnamedRouteException;
use Glhd\Gretel\Exceptions\UnresolvableParentException;
use Glhd\Gretel\Support\Facades\Gretel;
use Illuminate\Support\Facades\Route;
use Illuminate\View\ViewException;
use Throwable;

class ExceptionHandlingTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		
		$this->withoutExceptionHandling();
	}
	
	public function test_throwing_missing_breadcrumbs_exception(): void
	{
		Route::get('/', fn() => $this->renderBlade('<x-breadcrumbs />'))->name('home');
		
		Gretel::throwOnMissingBreadcrumbs(true);
		
		try {
			$this->withoutExceptionHandling()->get('/');
			$this->fail('No exception thrown.');
		} catch (ViewException $exception) {
			$previous = $exception->getPrevious();
			$this->assertInstanceOf(MissingBreadcrumbException::class, $previous);
		}
	}
	
	public function test_not_throwing_missing_breadcrumbs_exception(): void
	{
		Route::get('/', fn() => $this->renderBlade('[BEFORE]<x-breadcrumbs />[AFTER]'))->name('home');
		
		Gretel::throwOnMissingBreadcrumbs(false);
		
		$this->get('/')
			->assertOk()
			->assertSee('[BEFORE][AFTER]');
	}
	
	public function test_handling_missing_breadcrumbs_exception(): void
	{
		Route::get('/', fn() => $this->renderBlade('[BEFORE]<x-breadcrumbs />[AFTER]'))->name('home');
		
		$handled = null;
		Gretel::handleMissingBreadcrumbs(function($exception) use (&$handled) {
			$handled = $exception;
		});
		
		$this->get('/')
			->assertOk()
			->assertSee('[BEFORE][AFTER]');
		
		$this->assertInstanceOf(MissingBreadcrumbException::class, $handled);
	}
	
	public function test_throwing_misconfigured_breadcrumbs_exception(): void
	{
		Route::get('/misconfigured', fn() => $this->renderBlade('<x-breadcrumbs />'))
			->name('misconfigured')
			->breadcrumb('Mis-configured', 'this-does-not-exist');
		
		Gretel::throwOnMisconfiguredBreadcrumbs(true);
		
		try {
			$this->withoutExceptionHandling()->get('/misconfigured');
			$this->fail('No exception thrown.');
		} catch (ViewException $exception) {
			$previous = $exception->getPrevious();
			$this->assertInstanceOf(UnresolvableParentException::class, $previous);
		}
	}
	
	public function test_not_throwing_misconfigured_breadcrumbs_exception(): void
	{
		Route::get('/misconfigured', fn() => $this->renderBlade('[BEFORE]<x-breadcrumbs />[AFTER]'))
			->name('misconfigured')
			->breadcrumb('Mis-configured', 'this-does-not-exist');
		
		Gretel::throwOnMisconfiguredBreadcrumbs(false);
		
		$this->get('/misconfigured')
			->assertOk()
			->assertSee('[BEFORE][AFTER]');
	}
	
	public function test_handling_misconfigured_breadcrumbs_exception(): void
	{
		Route::get('/misconfigured', fn() => $this->renderBlade('[BEFORE]<x-breadcrumbs />[AFTER]'))
			->name('misconfigured')
			->breadcrumb('Mis-configured', 'this-does-not-exist');
		
		$handled = null;
		Gretel::handleMisconfiguredBreadcrumbs(function($exception) use (&$handled) {
			$handled = $exception;
		});
		
		$this->get('/misconfigured')
			->assertOk()
			->assertSee('[BEFORE][AFTER]');
		
		$this->assertInstanceOf(UnresolvableParentException::class, $handled);
	}
	
	public function test_handling_misconfigured_breadcrumbs_exception_in_macro(): void
	{
		$handled = null;
		Gretel::handleMisconfiguredBreadcrumbs(function(Throwable $exception) use (&$handled) {
			$handled = $exception;
		});
		
		Route::get('/misconfigured', fn() => $this->renderBlade('[BEFORE]<x-breadcrumbs />[AFTER]'))
			->breadcrumb('Mis-configured', 'has-no-name');
		
		$this->assertInstanceOf(UnnamedRouteException::class, $handled);
		
		$this->get('/misconfigured')
			->assertOk()
			->assertSee('[BEFORE][AFTER]');
	}
}
