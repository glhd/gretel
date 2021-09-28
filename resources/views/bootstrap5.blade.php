<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumbs">
		<ol class="breadcrumb">
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="breadcrumb-item {{ $activeClass('active') }}" {{ $ariaCurrent() }}>
					@if($breadcrumb->is_current_page)
						{{ $breadcrumb->title }}
					@else
						<a href="{{ $breadcrumb->url }}">
							{{ $breadcrumb->title }}
						</a>
				@endif
			@endforeach
		</ol>
	</nav>
@endunless
