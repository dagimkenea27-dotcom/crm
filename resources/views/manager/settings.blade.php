@extends('layouts.app')

@section('title', 'Team Settings')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 class="page-title">Team & Compensation Settings</h1>
        <p class="page-subtitle">Manage contractor accounts, commission rates, and assigned coupon codes</p>
    </div>
    <button class="btn btn-primary" onclick="openAddUserModal()" style="display:inline-flex; align-items:center; gap:8px;">
        <svg viewBox="0 0 24 24" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Team Member
    </button>
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
    document.getElementById('editUserForm').action = '/settings/users/' + user.id;
    document.getElementById('editName').value = user.name || '';
    document.getElementById('editCoupon').value = user.coupon_code || '';
    document.getElementById('editFixed').value = user.fixed_pay_birr || '0.00';
    document.getElementById('editComm').value = user.commission_birr || '0.00';
    document.getElementById('editUserModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}
</script>
@endpush
@endsection
