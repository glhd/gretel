<?php

namespace Glhd\Gretel\View\Components;

use Glhd\Gretel\Exceptions\MissingBreadcrumbException;
use Glhd\Gretel\Routing\Breadcrumbs as RouteBreadcrumbs;
use Illuminate\Config\Repository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
	protected RouteBreadcrumbs $breadcrumbs;
	
	protected Repository $config;
	
	protected ?string $view = null;
	
	protected bool $throw = false;
	
	public function __construct(
		RouteBreadcrumbs $breadcrumbs,
		Repository $config,
		string $framework = null,
		bool $jsonLd = false,
		bool $throwIfMissing = false
	) {
		$this->breadcrumbs = $breadcrumbs;
		$this->config = $config;
		$this->throw = $throwIfMissing;
		
		if ($jsonLd) {
			$this->view = 'gretel::json-ld';
		} elseif ($framework) {
			$this->view = "gretel::{$framework}";
		}
	}
	
	public function render()
	{
		$view = $this->view ?? $this->config->get('gretel.view', 'gretel::tailwind');
		$breadcrumbs = $this->breadcrumbs->toCollection();
		
		if ($this->throw && $breadcrumbs->isEmpty()) {
			throw new MissingBreadcrumbException(URL::current()); // FIXME
		}
		
		return view($view, [
			'breadcrumbs' => $breadcrumbs,
			'jsonld' => fn(int $flags = 0) => $this->convertBreadcrumbsToJsonLinkingData($breadcrumbs, $flags),
		]);
	}
	
	protected function convertBreadcrumbsToJsonLinkingData(Collection $breadcrumbs, int $flags = 0): HtmlString
	{
		$items = $breadcrumbs
			->values()
			->map(function($breadcrumb, $index) {
				return [
					'@type' => 'ListItem',
					'position' => $index + 1,
					'item' => [
						'@id' => $breadcrumb->url,
						'name' => $breadcrumb->title,
						// 'image' => null,
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
}
