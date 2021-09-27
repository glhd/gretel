@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="uk-breadcrumb">
			@foreach ($breadcrumbs as $breadcrumb)
				<li>
					@unless($loop->last)
						<a href="{{ $breadcrumb->url }}">
							{{ $breadcrumb->title }}
						</a>
					@else
						<span aria-current="page">{{ $breadcrumb->title }}</span>
					@endif
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
