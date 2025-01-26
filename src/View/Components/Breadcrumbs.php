<?php

namespace Glhd\Gretel\View\Components;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Routing\RequestBreadcrumbs;
use Glhd\Gretel\View\Breadcrumb;
use Glhd\Gretel\View\BreadcrumbCollection;
use Illuminate\Config\Repository;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
	public BreadcrumbCollection $breadcrumbs;
	
	protected Repository $config;
	
	protected ?string $view = null;
	
	public function __construct(
		RequestBreadcrumbs $breadcrumbs,
		Registry $registry,
		Repository $config,
		?string $framework = null,
		?string $view = null,
		bool $jsonLd = false,
		bool $rdfa = false
	) {
		// First, we'll initialize to an empty collection
		$this->breadcrumbs = new BreadcrumbCollection();
		
		// Then we'll try to load the breadcrumbs, passing exceptions on to any configured handlers
		$registry->withExceptionHandling(function() use ($breadcrumbs) {
			$this->breadcrumbs = $breadcrumbs->toCollection();
			$breadcrumbs->throwIfMissing();
		});
		
		$this->config = $config;
		
		if ($view) {
			$this->view = $view;
		} elseif ($jsonLd) {
			$this->view = 'gretel::json-ld';
		} elseif ($framework) {
			$this->view = "gretel::{$framework}";
		}
	}
	
	public function render()
	{
		$view = $this->view ?? $this->config->get('gretel.view', 'gretel::tailwind');
		
		return view($view);
	}
	
	public function activeClass(string ...$class): ?string
	{
		if (! $this->breadcrumbs->active->is_current_page) {
			return null;
		}
		
		return collect($class)->filter()->implode(' ');
	}
	
	public function inactiveClass(string ...$class): ?string
	{
		if ($this->breadcrumbs->active->is_current_page) {
			return null;
		}
		
		return collect($class)->filter()->implode(' ');
	}
	
	public function ariaCurrent(string $value = 'page'): ?HtmlString
	{
		if (! $this->breadcrumbs->active->is_current_page) {
			return null;
		}
		
		return new HtmlString(' aria-current="'.e($value).'" ');
	}
	
	public function href(): HtmlString
	{
		return new HtmlString(' href="'.e($this->breadcrumbs->active->url).'" ');
	}
	
	public function jsonld(int $flags = 0): HtmlString
	{
		$items = $this->breadcrumbs->values()
			->map(function(Breadcrumb $breadcrumb, $index) {
				return [
					'@type' => 'ListItem',
					'position' => $index + 1,
					'item' => [
						'@id' => $breadcrumb->url,
						'name' => $breadcrumb->title,
					],
				];
			})
			->all();
		
		return new HtmlString(json_encode([
			'@context' => 'https://schema.org',
			'@type' => 'BreadcrumbList',
			'itemListElement' => $items,
		], JSON_THROW_ON_ERROR | $flags));
	}
	
	protected function getBreadcrumbCollection(RequestBreadcrumbs $breadcrumbs, Registry $registry): BreadcrumbCollection
	{
		try {
			return $breadcrumbs->toCollection();
		} catch (MissingBreadcrumbException $exception) {
			// $registry->
		}
	}
}
