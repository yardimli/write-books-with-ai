@extends('layouts.app')

@section('title', 'Terms')

@section('content')
	<!-- **************** MAIN CONTENT START **************** -->
	<main>
		
		<!-- Container START -->
		<div class="container mt-5">
			<div class="row">
			<form action="/users" method="GET" class="col-9">
				<div class="input-group mb-3">
					<input name="search" type="text" class="form-control" placeholder="Search users"
					       value="{{ request('search') }}">
					<button class="btn btn-primary" type="submit">Search</button>
				</div>
			</form>
				<div class="col-3">
					<a href="/users?purchase=yes" class="btn btn-primary">Purchased</a>
					<a href="/users?written=yes" class="btn btn-primary">Written</a>
				</div>
			</div>
			
			<table class="table table-bordered">
				<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Created</th>
				</tr>
				</thead>
				<tbody>
				@foreach($users as $user)
					<tr style="background-color: #222;">
						<td>{{ $user->name }}</td>
						<td>{{ $user->email }}</td>
						<td>{{ $user->created_at }}</td>
						<td>
							<form action="{{ route('users-login-as') }}" method="POST">
								@csrf
								<input type="hidden" name="user_id" value="{{ $user->id }}"/>
								<button type="submit" class="btn btn-primary btn-sm">Login As</button>
							</form>
						</td>
					</tr>
					@if ($user->user_order_and_tokens['orders']->count() > 1)
						<tr>
							<td colspan="3" style="padding-left:30px;">
								Orders:<br>
									@foreach($user->user_order_and_tokens['orders'] as $order)
{{ $order['order_id'] }} - {{ $order['total_amount'] }} -
									@foreach($order['items'] as $item)
													<span class="small text-body">{{ $item['product_name'] }} - ${{ $item['price'] }} - {{ $item['reference_id'] }}</span>
												@endforeach
							- {{ $order['order_date'] }}<br>
									@endforeach
							</td>
						</tr>
					@endif
					
					<tr>
						<td colspan="3" style="padding-left:30px;">
						<b>Total GPT-3.5 Credits:</b> : {{ number_format($user->user_order_and_tokens['gpt3_5_credits']) }} - {{ number_format($user->user_order_and_tokens['gpt3_5_credits_used']) }}
							----
							<b>Total GPT-4 Credits</b> : {{ number_format($user->user_order_and_tokens['gpt4_credits']) }} - {{ number_format($user->user_order_and_tokens['gpt4_credits_used']) }}
						</td>
					</tr>
					
					
					@if ($user->stories->count() > 0)
						<tr>
							<td colspan="3" style="padding-left:30px;">
									@foreach($user->stories as $story)
<a class="nav-link" style="display: inline-block;" href="{{ url('read-story/' . $story->chat_header_guid) }}">{{ $story->title }}</a> ({{ $story->total_word_count }} words) - {{ $story->created_at }} - {{ $story->is_header_deleted ? 'deleted - ' : '' }} {{ $story->is_private ? 'private - ' : '' }} {{ $story->nsfw ? 'nsfw - ' : '' }}  {{ str_replace(',',', ', $story->nsfw_reason) }}
							<br>
									@endforeach
							</td>
						</tr>
					@endif
				@endforeach
				</tbody>
			</table>
			
			<!-- Pagination Links -->
			<?php
				$users = $users->appends(['purchase' => $_GET['purchase'] ?? 'no', 'written' => $_GET['written'] ?? 'no', 'search' => $_GET['search'] ?? '' ]);

			?>
			<div class="d-flex justify-content-center">
				@if ($users->onFirstPage())
					<button class="btn btn-secondary mx-1" disabled>First</button>
				@else
					<a href="{{ $users->url(1) }}" class="btn btn-primary mx-1">First</a>
				@endif
				
				@if ($users->onFirstPage())
					<button class="btn btn-secondary mx-1" disabled>Previous</button>
				@else
					<a href="{{ $users->previousPageUrl() }}" class="btn btn-primary mx-1">Previous</a>
				@endif
				
				@foreach(range(1, $users->lastPage()) as $i)
					@if ($i >= $users->currentPage() - 2 && $i <= $users->currentPage() + 2)
						@if ($i == $users->currentPage())
							<button class="btn btn-secondary mx-1">{{ $i }}</button>
						@else
							<a href="{{ $users->url($i) }}" class="btn btn-primary mx-1">{{ $i }}</a>
						@endif
					@endif
				@endforeach
				
				@if ($users->hasMorePages())
					<a href="{{ $users->nextPageUrl() }}" class="btn btn-primary mx-1">Next</a>
				@else
					<button class="btn btn-secondary mx-1" disabled>Next</button>
				@endif
				
				@if ($users->currentPage() === $users->lastPage())
					<button class="btn btn-secondary mx-1" disabled>Last</button>
				@else
					<a href="{{ $users->url($users->lastPage()) }}" class="btn btn-primary mx-1">Last</a>
				@endif
			</div>
			
			<p>Viewing {{ $users->firstItem() }} - {{ $users->lastItem() }} out of {{ $users->total() }}</p>
		
		</div>
	</main>
	<!-- **************** MAIN CONTENT END **************** -->
	
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<script>
		var current_page = 'privacy';
		$(document).ready(function () {
		});
	</script>
@endpush
