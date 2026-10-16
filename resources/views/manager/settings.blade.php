@extends('layouts.app')

@section('title', 'Admin Settings & Team')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 class="page-title">Settings & Administration</h1>
        <p class="page-subtitle">Manage your administrator profile, account security, and contractor team settings</p>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <a href="#admin-profile" class="btn btn-secondary" style="display:inline-flex; align-items:center; gap:6px;">
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Admin Profile
        </a>
        <button class="btn btn-primary" onclick="openAddUserModal()" style="display:inline-flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Team Member
        </button>
    </div>
</div>

<!-- Admin Profile & Security Grid -->
<div id="admin-profile" class="grid-2" style="margin-bottom:28px;">
    <!-- Profile Information Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--border);">
            <div>
                <h2 class="card-title" style="display:flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 24 24" width="18" height="18" style="color:var(--primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile Information
                </h2>
                <p class="card-subtitle">Update your personal administrator account details</p>
            </div>
            <span class="badge badge-primary">{{ ucfirst($admin->role ?? 'Manager') }}</span>
        </div>

        <form method="POST" action="{{ route('settings.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="admin-name" class="form-label">Full Name *</label>
                <input type="text" id="admin-name" name="name" class="form-input" required autocomplete="name"
                       value="{{ old('name', $admin->name ?? auth()->user()->name) }}" placeholder="e.g. John Doe">
                @error('name')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="admin-email" class="form-label">Email Address *</label>
                <input type="email" id="admin-email" name="email" class="form-input" required autocomplete="email"
                       value="{{ old('email', $admin->email ?? auth()->user()->email) }}" placeholder="admin@example.com">
                @error('email')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="admin-role-display" class="form-label">System Role & Access</label>
                <input type="text" id="admin-role-display" class="form-input" disabled readonly
                       value="{{ ucfirst($admin->role ?? auth()->user()->role) }} (Full Administrator Access)"
                       style="background:#f8fafc; color:var(--text-muted); cursor:not-allowed;">
                <div class="form-hint">Managers have full access to order verification, customer reassignments, imports, and team payouts.</div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 24 24" width="16" height="16"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Security & Password Card -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--border);">
            <div>
                <h2 class="card-title" style="display:flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 24 24" width="18" height="18" style="color:var(--primary);"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Change Password
                </h2>
                <p class="card-subtitle">Ensure your account is secured with a strong password</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password" class="form-label">Current Password *</label>
                <div style="position:relative;">
                    <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password" placeholder="Enter current password" style="padding-right:40px;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('current_password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('current_password')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password" class="form-label">New Password *</label>
                <div style="position:relative;">
                    <input type="password" id="new_password" name="password" class="form-input" required autocomplete="new-password" minlength="8" placeholder="Minimum 8 characters" style="padding-right:40px;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('new_password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @error('password')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm New Password *</label>
                <div style="position:relative;">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required autocomplete="new-password" minlength="8" placeholder="Repeat new password" style="padding-right:40px;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;" aria-label="Toggle password visibility">
                        <svg viewBox="0 0 24 24" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 24 24" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Team Section Header -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
    <div>
        <h2 style="font-size:16px; font-weight:700; color:var(--text);">Team Members & Compensation</h2>
        <p style="font-size:12px; color:var(--text-muted); margin-top:2px;">Contractor accounts, commission rates, and assigned coupon codes</p>
    </div>
</div>

<!-- Team Table Card -->
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Contractor / Member</th>
                    <th>Role</th>
                    <th>Coupon Code</th>
                    <th>Fixed Pay</th>
                    <th>Commission</th>
                    <th>Leads Assigned</th>
                    <th>Verified Orders</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contractors as $user)
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--text);">{{ $user->name }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">{{ $user->email }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $user->isManager() ? 'badge-primary' : 'badge-neutral' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        @if($user->coupon_code)
                            <span class="badge badge-primary" style="font-family:monospace; font-size:12px;">
                                {{ $user->coupon_code }}
                            </span>
                        @else
                            <span style="font-size:12px; color:var(--text-light);">&mdash;</span>
                        @endif
                    </td>
                    <td style="font-weight:600;">
                        {{ number_format($user->fixed_pay_birr, 2) }} ETB
                    </td>
                    <td style="font-weight:600; color:var(--success);">
                        {{ number_format($user->commission_birr, 2) }} ETB
                    </td>
                    <td>
                        <span class="badge badge-neutral" style="font-weight:600;">
                            {{ $user->assigned_customers_count }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-success" style="font-weight:600;">
                            {{ $user->verified_count }}
                        </span>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button class="btn btn-sm btn-secondary" onclick='openEditModal(@json($user))' style="margin-right:6px;">
                            Edit
                        </button>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('settings.users.destroy', $user) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted);">
                        No contractors registered yet. Click "Add Team Member" above to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="background:var(--surface); border-radius:var(--radius-lg); max-width:540px; width:100%; padding:24px; box-shadow:var(--shadow-lg); position:relative;">
        <button onclick="closeAddUserModal()" style="position:absolute; right:18px; top:18px; background:transparent; border:none; cursor:pointer; color:var(--text-muted);">
            <svg viewBox="0 0 24 24" width="22" height="22"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <h2 style="font-size:18px; font-weight:700; margin-bottom:4px; color:var(--text);">Add Team Member</h2>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">Create a new manager or contractor login account</p>

        <form method="POST" action="{{ route('settings.users.store') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-input" required placeholder="e.g. Hanna Kebede">
                </div>
                <div>
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" required placeholder="hanna@gojoshop.com">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        <option value="contractor">Contractor (Sales Agent)</option>
                        <option value="manager">Manager (Admin)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-input" required placeholder="Minimum 8 characters">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label class="form-label">Coupon Code</label>
                <input type="text" name="coupon_code" class="form-input" placeholder="e.g. HANNA_100 (optional)">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                <div>
                    <label class="form-label">Fixed Pay (Birr / ETB)</label>
                    <input type="number" step="0.01" name="fixed_pay_birr" class="form-input" value="0.00">
                </div>
                <div>
                    <label class="form-label">Commission (Birr per Order)</label>
                    <input type="number" step="0.01" name="commission_birr" class="form-input" value="0.00">
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-neutral" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="background:var(--surface); border-radius:var(--radius-lg); max-width:540px; width:100%; padding:24px; box-shadow:var(--shadow-lg); position:relative;">
        <button onclick="closeEditModal()" style="position:absolute; right:18px; top:18px; background:transparent; border:none; cursor:pointer; color:var(--text-muted);">
            <svg viewBox="0 0 24 24" width="22" height="22"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <h2 style="font-size:18px; font-weight:700; margin-bottom:4px; color:var(--text);">Edit Member Details</h2>
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:18px;">Update compensation figures, coupon code, or reset password</p>

        <form id="editUserForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div style="margin-bottom:12px;">
                <label class="form-label">Full Name *</label>
                <input type="text" id="editName" name="name" class="form-input" required>
            </div>

            <div style="margin-bottom:12px;">
                <label class="form-label">Coupon Code</label>
                <input type="text" id="editCoupon" name="coupon_code" class="form-input">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                <div>
                    <label class="form-label">Fixed Pay (Birr / ETB)</label>
                    <input type="number" step="0.01" id="editFixed" name="fixed_pay_birr" class="form-input">
                </div>
                <div>
                    <label class="form-label">Commission (Birr per Order)</label>
                    <input type="number" step="0.01" id="editComm" name="commission_birr" class="form-input">
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-input" placeholder="Optional">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-neutral" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}
function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}
function openEditModal(user) {
    document.getElementById('editUserForm').action = '{{ url('settings/users') }}/' + user.id;
    document.getElementById('editName').value = user.name || '';
    document.getElementById('editCoupon').value = user.coupon_code || '';
    document.getElementById('editFixed').value = user.fixed_pay_birr || '0.00';
    document.getElementById('editComm').value = user.commission_birr || '0.00';
    document.getElementById('editUserModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
        btn.setAttribute('aria-label', 'Hide password');
    } else {
        input.type = 'password';
        btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        btn.setAttribute('aria-label', 'Show password');
    }
}
</script>
@endpush
@endsection
