@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="breadcrumb">
			@foreach ($breadcrumbs as $breadcrumb)
				@if($loop->last)
					<li class="breadcrumb-item active" aria-current="page">
						{{ $breadcrumb->title }}
					</li>
				@else
					<li class="breadcrumb-item">
						<a href="{{ $breadcrumb->url }}">
							{{ $breadcrumb->title }}
						</a>
					</li>
				@endif
			@endforeach
		</ol>
	</nav>
@endunless
