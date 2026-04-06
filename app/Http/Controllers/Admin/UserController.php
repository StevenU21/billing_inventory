<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProfileData;
use App\DTOs\UserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AutocompleteService;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = QueryBuilder::for(User::class)
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->whereHas('roles', function ($q) use ($value) {
                        $q->where('name', $value);
                    });
                }),
                AllowedFilter::callback('status', function ($query, $value) {
                    if ($value === 'activo') {
                        $query->where('is_active', true);
                    } elseif ($value === 'inactivo') {
                        $query->where('is_active', false);
                    }
                }),
                AllowedFilter::callback('gender', function ($query, $value) {
                    $query->whereHas('profile', function ($q) use ($value) {
                        $q->where('gender', $value);
                    });
                }),
            ])
            ->allowedSorts(...['id', 'first_name', 'last_name', 'email', 'is_active', 'created_at'])
            ->defaultSort('-id')
            // Eager loading
            ->with(['roles', 'profile'])
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    // New Autocomplete for Index Search
    public function autocomplete(Request $request, AutocompleteService $autocompleteService)
    {
        $this->authorize('viewAny', User::class);
        $term = $request->input('q', '');
        $limit = $request->input('limit', 10);

        $query = User::query()->where('is_active', true);
        $results = $autocompleteService->search($query, $term, ['first_name', 'last_name'], $limit);

        return $autocompleteService->response($results, function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->full_name,
            ];
        });
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        $user->load(['roles.permissions', 'profile']);

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $roles = Role::all();
        $user = new User;

        return view('admin.users.create', compact('roles', 'user'));
    }

    public function store(UserRequest $request, ProfileRequest $profileRequest, UserService $userService)
    {
        $userData = UserData::fromRequest($request->validated());
        $profileData = ProfileData::fromRequest($profileRequest->validated());

        $userService->createUser($userData, $profileData, $profileRequest->file('avatar'));

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $user->load(['roles', 'profile']);
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user, UserService $userService)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        // Check if role is an array (validation error)
        $role = $request->input('role');
        if (is_array($role)) {
            return redirect()->back()->withErrors(['role' => 'Solo se puede asignar un rol a la vez']);
        }

        $userData = UserData::fromRequest($request->validated());
        $profileData = ProfileData::fromRequest($request->only(['phone', 'identity_card', 'gender', 'address', 'avatar']));

        $userService->updateUser($user, $userData, $profileData, $request->file('avatar'));

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user, UserService $userService)
    {
        $this->authorize('destroy', $user);

        $wasActive = $user->is_active;
        $userService->toggleUserStatus($user);

        if ($wasActive) {
            return redirect()->route('users.index')->with('updated', 'Usuario desactivado correctamente');
        } else {
            $this->authorize('update', $user);

            return redirect()->route('users.index')->with('deleted', 'Usuario reactivado correctamente.');
        }
    }
}
