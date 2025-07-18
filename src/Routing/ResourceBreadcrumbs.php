<?php

namespace Glhd\Gretel\Routing;

use Closure;
use Glhd\Gretel\Registry;
use Glhd\Gretel\Resolvers\ParentResolver;
use Glhd\Gretel\Resolvers\TitleResolver;
use Glhd\Gretel\Resolvers\UrlResolver;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use stdClass;
use Throwable;

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
	
	public function index($title, $parent = null, ?Closure $relation = null): self
	{
		return $this->configureAction('index', $title, $parent, $relation);
	}
	
	public function create($title, $parent = null, ?Closure $relation = null): self
	{
		return $this->configureAction('create', $title, $parent, $relation);
	}
	
	public function show($title, $parent = null, ?Closure $relation = null): self
	{
		return $this->configureAction('show', $title, $parent, $relation);
	}
	
	public function edit($title, $parent = null, ?Closure $relation = null): self
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
		$action = $names[$action] ?? "{$this->name}.{$action}";
		
		return trim(Route::mergeWithLastGroup(['as' => $action])['as'], '.');
	}
	
	protected function getParameterNamesForAction(string $action): array
	{
		$parameters = $this->getRouteGroupParameters();
		
		if (in_array($action, ['show', 'edit'])) {
			$parameters[] = $this->getResourceWildcard();
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
	
	protected function getResourceWildcard(): string
	{
		return str_replace('-', '_', $this->getRawResourceWildcard());
	}
	
	/** @see \Illuminate\Routing\ResourceRegistrar::getResourceWildcard() */
	protected function getRawResourceWildcard(): string
	{
		$value = $this->name;
		
		if (isset($this->options['parameters'][$value])) {
			return $this->options['parameters'][$value];
		}
		
		$global_parameters = ResourceRegistrar::getParameters();
		if (isset($global_parameters[$value])) {
			return $global_parameters[$value];
		}
		
		if (isset($this->options['parameters']) && 'singular' === $this->options['parameters']) {
			return Str::singular($value);
		}
		
		// ResourceRegistrar::$singularParameters is protected, so we need to be a little careful about how we rely on it.
		try {
			$singular_by_default = (new ReflectionClass(ResourceRegistrar::class))
				->getStaticPropertyValue('singularParameters', true);
			
			if ($singular_by_default) {
				return Str::singular($value);
			}
		} catch (Throwable $exception) {
		}
			
		return $value;
	}
}
