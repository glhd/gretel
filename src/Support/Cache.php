<?php

namespace Glhd\Gretel\Support;

use Glhd\Gretel\Registry;
use Glhd\Gretel\Resolvers\Resolver;
use Glhd\Gretel\Routing\RouteBreadcrumb;
use Illuminate\Filesystem\Filesystem;

class Cache
{
	protected Filesystem $filesystem;
	
	protected string $path;
	
	public function __construct(Filesystem $filesystem, string $path)
	{
		$this->filesystem = $filesystem;
		$this->path = $path;
	}
	
	public function path(): string
	{
		return $this->path;
	}
	
	public function exists(): bool
	{
		return $this->filesystem->exists($this->path);
	}
	
	public function load(): bool
	{
		if ($this->exists()) {
			$this->filesystem->getRequire($this->path);
			return true;
		}
		
		return false;
	}
	
	public function clear(): bool
	{
		if (! $this->exists()) {
			return true;
		}
		
		return $this->filesystem->delete($this->path);
	}
	
	public function write(Registry $registry): bool
	{
		$contents = $this->generateCacheFile($registry);
		
		return false !== $this->filesystem->put($this->path, $contents);
	}
	
	protected function generateCacheFile(Registry $registry)
	{
		$breadcrumbs = $registry
			->map(fn(RouteBreadcrumb $breadcrumb) => $this->exportBreadcrumb($breadcrumb))
			->implode(",\n");
		
		$registry_class = Registry::class;
		$breadcrumb_class = RouteBreadcrumb::class;
		
		return <<<PHP
		<?php
		
		use {$registry_class};
		use {$breadcrumb_class};
		
		app(Registry::class)->register(
		{$breadcrumbs}
		);
		
		PHP;
	}
	
	protected function exportBreadcrumb(RouteBreadcrumb $breadcrumb): string
	{
		$name = var_export($breadcrumb->name, true);
		$title = $this->exportResolver($breadcrumb->title);
		$parent = $this->exportResolver($breadcrumb->parent);
		$url = $this->exportResolver($breadcrumb->url);
		
		return <<<PHP
			// '{$breadcrumb->name}'
			new RouteBreadcrumb(
				$name,
				$title,
				$parent,
				$url
			)
		PHP;
	}
	
	protected function exportResolver(Resolver $resolver): string
	{
		$fqcn = get_class($resolver);
		$callback = var_export($resolver->getSerializedClosure(), true);
		
		return "new \\{$fqcn}({$callback})";
	}
}
