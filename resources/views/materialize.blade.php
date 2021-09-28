<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<div class="nav-wrapper">
			<div class="col s12">
				@foreach ($breadcrumbs as $breadcrumb)
					<a href="{{ $breadcrumb->url }}" class="breadcrumb" {{ $ariaCurrent() }}>
						{{ $breadcrumb->title }}
					</a>
				@endforeach
			</div>
		</div>
	</nav>
@endunless
