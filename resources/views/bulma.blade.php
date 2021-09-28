<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav class="breadcrumb" aria-label="Breadcrumbs">
		<ol>
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="{{ $activeClass('is-active') }}">
					<a href="{{ $breadcrumb->url }}" {{ $ariaCurrent() }}>
						{{ $breadcrumb->title }}
					</a>
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
