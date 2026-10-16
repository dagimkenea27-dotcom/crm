<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $contractors = User::where('role', 'contractor')->withCount([
            'assignedCustomers',
            'assignedCustomers as verified_count' => fn($q) => $q->whereNotNull('verified_order_id'),
        ])->get();

        return view('manager.settings', compact('contractors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|min:8',
            'role'            => 'required|in:manager,contractor',
            'coupon_code'     => 'nullable|string|unique:users,coupon_code',
            'fixed_pay_birr'  => 'nullable|numeric|min:0',
            'commission_birr' => 'nullable|numeric|min:0',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'coupon_code'     => "nullable|string|unique:users,coupon_code,{$user->id}",
            'fixed_pay_birr'  => 'nullable|numeric|min:0',
            'commission_birr' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, "Can't delete yourself.");
        $user->delete();
        return back()->with('success', 'User removed.');
    }
}
