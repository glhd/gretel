@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumbs">
		<ol class="breadcrumbs">
			@foreach ($breadcrumbs as $breadcrumb)
				@foreach ($breadcrumbs as $breadcrumb)
					<li class="{{ $loop->last ? 'current' : '' }}">
						<a
							href="{{ $breadcrumb->url }}"
							aria-current="{{ $loop->last ? 'page' : 'false' }}"
						>
							{{ $breadcrumb->title }}
						</a>
					</li>
				@endforeach
			@endforeach
		</ol>
	</nav>
@endunless
