<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="breadcrumbs">
			@foreach ($breadcrumbs as $breadcrumb)
				<li {{ $ariaCurrent() }}>
					@if($breadcrumb->is_current_page)
						{{ $breadcrumb->title }}
					@else
						<a href="{{ $breadcrumb->url }}">
							{{ $breadcrumb->title }}
						</a>
					@endif
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
