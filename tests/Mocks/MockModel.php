<?php

namespace Glhd\Gretel\Tests\Mocks;

use Illuminate\Contracts\Routing\UrlRoutable;

class MockModel implements UrlRoutable
{
	public ?int $id = null;
	
	public ?string $name = null;
	
	public function getRouteKey()
	{
		return $this->id;
	}
	
	public function getRouteKeyName()
	{
		return 'id';
	}
	
	public function resolveRouteBinding($value, $field = null)
	{
		$field ??= $this->getRouteKeyName();
		
		$model = new static();
		$model->id = $value;
		$model->name = implode(':', [class_basename($this), $value, $field]);
		
		return $model;
	}
	
	public function resolveChildRouteBinding($childType, $value, $field)
	{
		$model = new static();
		$model->id = $value;
		$model->name = implode(':', [class_basename($this), 'child', $childType, $value, $field]);
		
		return $model;
	}
}
