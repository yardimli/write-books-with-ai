<?php

	namespace App\Http\Controllers;

	use App\Helpers\MyHelper;
	use App\Models\ChatBody;
	use App\Models\ChatHeader;
	use Illuminate\Http\Request;
	use App\Models\User;
	use Illuminate\Pagination\LengthAwarePaginator;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;
	use Illuminate\Support\Facades\Validator;


	class UserController extends Controller
	{
		public function index(Request $request)
		{
			// Check if the logged-in user is user_id 1
			if (Auth::user()->id === 1) {
				// Fetch all users
				$query = User::query();

				if ($request->has('search')) {
					$query->where('name', 'like', "%{$request->search}%")
						->orWhere('email', 'like', "%{$request->search}%");
				}

//				$users = $query->paginate(200);
				$users = $query->orderBy('id', 'desc')->get();

				//if query purchase is yes
				if ($request->has('purchase') && $request->purchase === 'yes') {

					foreach ($users as $user) {
						$user->user_order_and_tokens = MyHelper::getUserOrdersAndTokens($user->id);
					}

					$users = $users->filter(function ($user) {
						return $user->user_order_and_tokens['gpt4_credits'] > 0;
					});
				}


				$users->map(function ($user) {
					$past_stories = ChatHeader::with('user')
						->where('user_id', $user->id)
						->orderBy('created_at', 'desc')
//						->limit(200)
						->get();

					$user->stories = $past_stories;
				});

				if ($request->has('written') && $request->written === 'yes') {

					$users = $users->filter(function ($user) {
						return count($user->stories) > 0;
					});
				}


				$page = LengthAwarePaginator::resolveCurrentPage() ?: 1;

				// Create a new LengthAwarePaginator instance
				$items = $users->forPage($page, 100);
				$users = new LengthAwarePaginator($items, $users->count(), 100, $page, [
					'path' => LengthAwarePaginator::resolveCurrentPath(),
				]);

				foreach ($users as $user) {
					$user->user_order_and_tokens = MyHelper::getUserOrdersAndTokens($user->id);
				}


				// Return to the users view
				return view('user.users', compact('users'));
			} else {
				abort(403, 'Unauthorized action.');
			}
		}

		public function loginAs(Request $request)
		{
			if (Auth::user()->id === 1) {
				Auth::loginUsingId($request->user_id);
				return redirect()->back();
			} else {
				abort(403, 'Unauthorized action.');
			}
		}



	}
