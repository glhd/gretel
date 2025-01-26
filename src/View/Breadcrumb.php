<?php

namespace Glhd\Gretel\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class Breadcrumb implements Arrayable, Jsonable, JsonSerializable
{
	public string $title;
	
	public string $url;
	
	public bool $is_current_page;
	
	public ?Breadcrumb $parent = null;
	
	public function __construct(
		string $title,
		string $url,
		bool $is_current_page = false,
		?Breadcrumb $parent = null
	) {
		$this->title = $title;
		$this->url = $url;
		$this->is_current_page = $is_current_page;
		$this->parent = $parent;
		
		// TODO: URL/svg, disabled
	}
	
	public function toArray(): array
	{
		return [
			'title' => $this->title,
			'url' => $this->url,
			'is_current_page' => $this->is_current_page,
		];
	}
	
	public function toJson($options = JSON_THROW_ON_ERROR)
	{
		return json_encode($this->jsonSerialize(), $options);
	}
	
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
