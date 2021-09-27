@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb" class="ui breadcrumb">
		@foreach ($breadcrumbs as $breadcrumb)
			@unless($loop->last)
				<a
					href="{{ $breadcrumb->url }}"
					class="section"
					aria-current="{{ $loop->last ? 'page' : 'false' }}"
				>
					{{ $breadcrumb->title }}
				</a>
				<div aria-hidden="true" class="divider"> / </div>
			@else
				<div class="active section">
					{{ $breadcrumb->title }}
				</div>
			@endunless
		@endforeach
	</nav>
@endunless
