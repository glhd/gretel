<?php

namespace Glhd\Gretel\Support;

use Glhd\Gretel\Registry;
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
		if (!$this->exists()) {
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
			->implode("\n\n");
		
		$registry_class = Registry::class;
		
		return <<<PHP
		<?php
		
		use {$registry_class};
		
		\$registry = app(Registry::class);

		{$breadcrumbs}
		
		PHP;
	}
	
	protected function exportBreadcrumb(RouteBreadcrumb $breadcrumb): string
	{
		$serialized = var_export(serialize($breadcrumb), true);
		
		return <<<PHP
		// '{$breadcrumb->name}' route
		\$registry->register(unserialize($serialized));
		PHP;
	}
}
