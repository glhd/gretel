<?php

namespace Glhd\Gretel\Routing;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Illuminate\Routing\RouteUri;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use stdClass;

class ResourceBreadcrumbs
{
	protected static array $parents = [
		'create' => '.index',
		'show' => '.index',
		'edit' => '.show',
	];
	
	protected string $name;
	
	protected array $options;
	
	protected array $actions = [];
	
	public function __construct(string $name, array $options = [])
	{
		$this->name = $name;
		$this->options = $options;
	}
	
	public function configure(array $config): self
	{
		foreach ($config as $action => $title) {
			$this->configureAction($action, $title);
		}
		
		return $this;
	}
	
	public function index($title, $parent = null, Closure $relation = null): self
	{
		return $this->configureAction('index', $title, $parent, $relation);
	}
	
	public function create($title, $parent = null, Closure $relation = null): self
	{
		return $this->configureAction('create', $title, $parent, $relation);
	}
	
	public function show($title, $parent = null, Closure $relation = null): self
	{
		return $this->configureAction('show', $title, $parent, $relation);
	}
	
	public function edit($title, $parent = null, Closure $relation = null): self
	{
		return $this->configureAction('edit', $title, $parent, $relation);
	}
	
	public function register(Registry $registry): void
	{
		$registry->withExceptionHandling(function(Registry $registry) {
			foreach ($this->actions as $action => $config) {
				$registry->register($this->makeBreadcrumbForAction($action, $config));
			}
		});
	}
	
	protected function configureAction(string $action, $title, $parent = null, ?Closure $relation = null): self
	{
		if (null === $parent && isset(static::$parents[$action])) {
			$parent = static::$parents[$action];
		}
		
		$this->actions[$action] = (object) compact('title', 'parent', 'relation');
		
		return $this;
	}
	
	protected function makeBreadcrumbForAction(string $action, stdClass $config): RouteBreadcrumb
	{
		if (is_string($config->parent)) {
			$config->parent = preg_replace_callback('/^\.([a-z_-]+)$/', fn($matches) => $this->getRouteNameForAction($matches[1]), $config->parent);
		}
		
		$name = $this->getRouteNameForAction($action);
		$title = TitleResolver::make($config->title);
		$parent = ParentResolver::make($config->parent, $name, $config->relation);
		$url = UrlResolver::make($name, $this->getParameterNamesForAction($action));
		
		return new RouteBreadcrumb($name, $title, $parent, $url);
	}
	
	/** @see \Illuminate\Routing\ResourceRegistrar::getResourceRouteName() */
	protected function getRouteNameForAction(string $action): string
	{
		$names = $this->options['names'] ?? [];

		if ($this->isShallow() && in_array($action, ['show', 'edit'])) {
			$action = $this->getShallowName().'.'.$action;
		} else {
			$action = $names[$action] ?? "{$this->name}.{$action}";
		}

		return trim(Route::mergeWithLastGroup(['as' => $action])['as'], '.');
	}
	
	protected function getParameterNamesForAction(string $action): array
	{
		$parameters = $this->getRouteGroupParameters();
		$name = $this->isShallow() ? $this->getShallowName() : $this->name;

		if (in_array($action, ['show', 'edit'])) {
			$parameters[] = $this->options['parameters'][$name] ?? Str::singular($name);
		}
		
		return $parameters;
	}
	
	protected function getRouteGroupParameters(): array
	{
		$pattern = '/\{([\w:]+?)\??}/'; // See RouteUri::parse()
		$prefix = Route::mergeWithLastGroup([])['prefix'] ?? '';

		preg_match_all($pattern, $prefix, $matches);

		return $matches[1] ?? [];
	}

	private function isShallow(): bool
	{
		return isset($this->options['shallow']) && $this->options['shallow'];
	}

	private function getShallowName(): string
	{
		return last(explode('.', $this->name));
	}
}
