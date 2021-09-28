<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol>
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="breadcrumb-item {{ $activeClass('breadcrumb-item-selected') }}">
					<a href="{{ $breadcrumb->url }}" {{ $ariaCurrent() }}>
						{{ $breadcrumb->title }}
					</a>
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
