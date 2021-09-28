<?php /** @var \Glhd\Gretel\View\BreadcrumbCollection|\Glhd\Gretel\View\Breadcrumb[] $breadcrumbs */ ?>

@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="uk-breadcrumb">
			@foreach ($breadcrumbs as $breadcrumb)
				<li>
					@if($breadcrumb->is_current_page)
						<span {{ $ariaCurrent() }}>
							{{ $breadcrumb->title }}
						</span>
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
