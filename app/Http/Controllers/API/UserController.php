<?php

    namespace App\Http\Controllers\API;

    use App\Http\Controllers\Controller;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;

    class UserController extends Controller
    {
        /**
         * Display a paginated listing of users.
         */
        public function index(Request $request)
        {
            $perPage = $request->integer('per_page', 10);

            $users = User::with('role')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'User list fetched successfully',
                'data' => $users
            ]);
        }

        /**
         * Store a newly created user.
         */
        public function store(Request $request)
        {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:6',
                'role_id'  => 'required|exists:roles,id',
            ]);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->load('role')
            ], 201);
        }

        /**
         * Update the specified user.
         */
        public function update(Request $request, int $id)
        {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',

                'username' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'username')->ignore($user->id)
                ],

                'password' => 'sometimes|nullable|string|min:6',

                'role_id' => 'sometimes|required|exists:roles,id',
            ]);

            // Remove password if empty
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load('role')
            ]);
        }

        /**
         * Remove the specified user.
         */
        public function destroy(int $id)
        {
            $user = User::findOrFail($id);

            // Prevent deleting own account (if authenticated)
            if (auth()->check() && auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        }
    }