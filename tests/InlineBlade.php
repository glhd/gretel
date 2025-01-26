<?php

namespace Glhd\Gretel\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;
use Illuminate\View\View;

class InlineBlade extends View
{
	protected Filesystem $fs;
	
	public function __construct(
		string $contents,
		$data = [],
		?Factory $factory = null,
		?Filesystem $fs = null
	) {
		$this->fs = $fs ?? new Filesystem();
		
		$factory ??= app('view');
		$engine = $factory->getEngineResolver()->resolve('blade');
		$view = sha1($contents);
		
		$path = tempnam(sys_get_temp_dir(), 'inline-blade');
		$this->fs->put($path, $contents);
		
		parent::__construct($factory, $engine, $view, $path, $data);
	}
	
	public function __destruct()
	{
		$this->fs->delete($this->path);
	}
}
