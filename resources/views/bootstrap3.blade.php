@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="breadcrumb">
			@foreach ($breadcrumbs as $breadcrumb)
				@if($loop->last)
					<li class="active" aria-current="page">
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
