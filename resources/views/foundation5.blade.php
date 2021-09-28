<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumbs">
		<ol class="breadcrumbs">
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="{{ $activeClass('current') }}">
					<a href="{{ $breadcrumb->url }}" {{ $ariaCurrent() }}>
						{{ $breadcrumb->title }}
					</a>
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
