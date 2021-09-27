@unless ($breadcrumbs->isEmpty())
	<nav class="breadcrumb" aria-label="breadcrumbs">
		<ol>
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="{{ $loop->last ? 'is-active' : '' }}">
					<a
						href="{{ $breadcrumb->url }}"
						aria-current="{{ $loop->last ? 'page' : 'false' }}"
					>
						{{ $breadcrumb->title }}
					</a>
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
