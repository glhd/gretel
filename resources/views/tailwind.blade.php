@unless ($breadcrumbs->isEmpty())
	<nav aria-label="Breadcrumb">
		<ol class="px-5 py-3 rounded flex flex-wrap bg-gray-100 text-sm">
			@foreach ($breadcrumbs as $breadcrumb)
				<li class="{{ $loop->last ? '' : 'mr-4' }}">
					<div class="flex items-center">
						<a
							href="{{ $breadcrumb->url }}"
							class="text-gray-500 hover:text-gray-800"
							aria-current="{{ $loop->last ? 'page' : 'false' }}"
						>
							{{ $breadcrumb->title }}
						</a>
						@unless($loop->last)
							<span aria-hidden="true" class="text-gray-300 ml-4 select-none">/</span>
						@endunless
					</div>
				</li>
			@endforeach
		</ol>
	</nav>
@endunless
