@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol>
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="breadcrumb-item {{ $loop->last ? 'breadcrumb-item-selected' : '' }}">
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
