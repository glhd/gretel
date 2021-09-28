<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb" class="ui breadcrumb">
		@foreach ($breadcrumbs as $breadcrumb)
			@if($breadcrumb->is_current_page)
				<div class="active section" {{ $ariaCurrent() }}>
					{{ $breadcrumb->title }}
				</div>
			@else
				<a href="{{ $breadcrumb->url }}" class="section">
					{{ $breadcrumb->title }}
				</a>
				<div aria-hidden="true" class="divider"> / </div>
			@endif
		@endforeach
	</nav>
@endunless
