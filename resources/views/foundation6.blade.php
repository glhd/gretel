@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumbs">
		<ol class="breadcrumbs">
			@foreach ($breadcrumbs as $breadcrumb)
				@if($loop->last)
					<li aria-current="page">
						{{ $breadcrumb->title }}
					</li>
				@else
					<li>
						<a href="{{ $breadcrumb->url }}">
							{{ $breadcrumb->title }}
						</a>
					</li>
				@endif
			@endforeach
		</ol>
	</nav>
@endunless
