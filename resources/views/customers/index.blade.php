@extends('layouts.app')

@section('title', auth()->user()->isManager() ? 'Customers' : 'My Customers')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div>
        <h1 class="page-title">{{ auth()->user()->isManager() ? 'Customer Directory' : 'My Assigned Leads' }}</h1>
        <p class="page-subtitle">Manage customer engagement, call queues, and order status</p>
    </div>
    @if(auth()->user()->isManager())
    <div style="display:flex; gap:10px;">
        <a href="{{ route('import.show') }}" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import Customers
        </a>
    </div>
    @endif
</div>

<!-- Tabs & Filter Bar -->
<div class="card" style="margin-bottom:20px; padding:16px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:14px; border-bottom:1px solid var(--border); padding-bottom:12px;">
        <!-- Status Tabs -->
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @php $currentTab = request('tab', ''); @endphp
            <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => ''])) }}"
               class="badge {{ $currentTab === '' ? 'badge-primary' : 'badge-neutral' }}"
               style="cursor:pointer; text-decoration:none; padding:8px 14px; font-size:13px; font-weight:600;">
                All
            </a>
            <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'not_called'])) }}"
               class="badge {{ $currentTab === 'not_called' ? 'badge-primary' : 'badge-neutral' }}"
               style="cursor:pointer; text-decoration:none; padding:8px 14px; font-size:13px; font-weight:600;">
                Not Called
            </a>
            <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'followup'])) }}"
               class="badge {{ $currentTab === 'followup' ? 'badge-primary' : 'badge-neutral' }}"
               style="cursor:pointer; text-decoration:none; padding:8px 14px; font-size:13px; font-weight:600;">
                Follow-up Due
            </a>
            <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'contacted'])) }}"
               class="badge {{ $currentTab === 'contacted' ? 'badge-primary' : 'badge-neutral' }}"
               style="cursor:pointer; text-decoration:none; padding:8px 14px; font-size:13px; font-weight:600;">
                Contacted
            </a>
            <a href="{{ route('customers.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'first_order'])) }}"
               class="badge {{ $currentTab === 'first_order' ? 'badge-primary' : 'badge-neutral' }}"
               style="cursor:pointer; text-decoration:none; padding:8px 14px; font-size:13px; font-weight:600;">
                First Orders
            </a>
        </div>

        <div style="color:var(--text-muted); font-size:13px; font-weight:500;">
            Showing <strong>{{ $customers->total() }}</strong> customers
        </div>
    </div>

    <!-- Filters form -->
    <form method="GET" action="{{ route('customers.index') }}" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
        <input type="hidden" name="tab" value="{{ request('tab') }}">

        <div style="flex:1; min-width:240px; position:relative;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or phone (e.g. 251911...)"
                   class="form-input" style="padding-left:36px;">
            <svg viewBox="0 0 24 24" width="16" height="16" style="position:absolute; left:12px; top:12px; stroke:var(--text-muted); pointer-events:none;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>

        <div style="min-width:180px;">
            <select name="filter" class="form-select" onchange="this.form.submit()">
                <option value="all" {{ request('filter') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="queue" {{ request('filter') === 'queue' ? 'selected' : '' }}>Call Queue (Active)</option>
                <option value="not_called" {{ request('filter') === 'not_called' ? 'selected' : '' }}>Not Called</option>
                <option value="callback" {{ request('filter') === 'callback' ? 'selected' : '' }}>Callback</option>
                <option value="interested" {{ request('filter') === 'interested' ? 'selected' : '' }}>Interested</option>
                <option value="not_ready" {{ request('filter') === 'not_ready' ? 'selected' : '' }}>Not Ready</option>
                <option value="ordered_unverified" {{ request('filter') === 'ordered_unverified' ? 'selected' : '' }}>Order Unverified</option>
                <option value="verified_order" {{ request('filter') === 'verified_order' ? 'selected' : '' }}>Verified Order</option>
                <option value="no_answer" {{ request('filter') === 'no_answer' ? 'selected' : '' }}>No Answer</option>
                <option value="do_not_contact" {{ request('filter') === 'do_not_contact' ? 'selected' : '' }}>Do Not Contact</option>
            </select>
        </div>

        @if(auth()->user()->isManager())
        <div style="min-width:180px;">
            <select name="contractor" class="form-select" onchange="this.form.submit()">
                <option value="">All Contractors</option>
                @foreach($contractors as $c)
                    <option value="{{ $c->id }}" {{ request('contractor') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <button type="submit" class="btn btn-secondary" style="padding:10px 16px;">Filter</button>
        @if(request()->hasAny(['q', 'filter', 'contractor', 'tab']))
            <a href="{{ route('customers.index') }}" class="btn btn-neutral" style="text-decoration:none; padding:10px 14px;">Clear</a>
        @endif
    </form>
</div>

<!-- Customer Table -->
<div class="card" style="padding:0; overflow:hidden;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    @if(auth()->user()->isManager())
                    <th>Assigned To</th>
                    @endif
                    <th>Status</th>
                    <th>Follow-up</th>
                    <th>Orders</th>
                    <th>Product</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                @php
                    $isOverdue = $customer->follow_up_on && $customer->follow_up_on->isPast() && !$customer->follow_up_on->isToday();
                    $isToday = $customer->follow_up_on && $customer->follow_up_on->isToday();
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--text);">{{ $customer->name ?: 'Unknown Customer' }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">
                            {{ $customer->location ?: 'No location' }}
                            @if($customer->joined_on)
                                &middot; Joined {{ $customer->joined_on->format('M d, Y') }}
                            @endif
                        </div>
                    </td>
                    <td>
                        <a href="tel:{{ $customer->phone }}" style="font-family:monospace; font-weight:600; color:var(--primary); text-decoration:none;">
                            {{ $customer->phone }}
                        </a>
                    </td>
                    @if(auth()->user()->isManager())
                    <td>
                        @if($customer->assignedTo)
                            <span class="badge badge-neutral" style="font-weight:500;">{{ $customer->assignedTo->name }}</span>
                        @else
                            <span class="badge badge-warning">Unassigned</span>
                        @endif
                    </td>
                    @endif
                    <td>
                        @php
                            $statusBadge = match($customer->activation_status) {
                                'verified_order' => 'badge-success',
                                'ordered_unverified' => 'badge-purple',
                                'interested' => 'badge-primary',
                                'callback', 'not_ready' => 'badge-warning',
                                'no_answer' => 'badge-neutral',
                                'do_not_contact' => 'badge-danger',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }}">
                            {{ $customer->status_label }}
                        </span>
                        @if($customer->coupon_offered)
                            <span class="badge badge-info" title="Coupon Offered" style="font-size:10px; margin-left:4px;">Coupon</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->follow_up_on)
                            <span style="font-size:12px; font-weight:600; color:{{ $isOverdue ? 'var(--danger)' : ($isToday ? 'var(--warning)' : 'var(--text-muted)') }};">
                                {{ $customer->follow_up_on->format('M d, Y') }}
                                @if($isOverdue) (Overdue) @elseif($isToday) (Today) @endif
                            </span>
                        @else
                            <span style="font-size:12px; color:var(--text-light);">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->verified_order_id)
                            <span class="badge badge-success" title="Verified: {{ $customer->verified_order_id }}">
                                {{ $customer->total_orders }} (Verified)
                            </span>
                        @else
                            <span style="font-weight:600; font-size:13px;">{{ $customer->total_orders }}</span>
                        @endif
                    </td>
                    <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:13px;" title="{{ $customer->product_name }}">
                        {{ $customer->product_name ?: '&mdash;' }}
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <button class="btn btn-sm btn-primary" onclick="openCallModal({{ $customer->id }})" style="display:inline-flex; align-items:center; gap:6px;">
                            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.92-8.39A2 2 0 0 1 3.46 2h3a2 2 0 0 1 2 1.72"/></svg>
                            Call / Log
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isManager() ? 8 : 7 }}" style="text-align:center; padding:48px 20px; color:var(--text-muted);">
                        <svg viewBox="0 0 24 24" width="48" height="48" style="stroke:var(--border); margin:0 auto 12px; display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <div style="font-size:16px; font-weight:600; color:var(--text); margin-bottom:4px;">No customers found</div>
                        <p style="font-size:13px; margin:0;">Try adjusting your search filters or upload leads via the Import page.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
    <div style="padding:16px 20px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="font-size:13px; color:var(--text-muted);">
            Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries
        </div>
        <div>
            {{ $customers->links() }}
        </div>
    </div>
    @endif
</div>

<!-- Call & Action Modal -->
<div id="callModal" class="modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-card" style="background:var(--surface); border-radius:var(--radius-lg); max-width:680px; width:100%; max-height:92vh; overflow-y:auto; box-shadow:var(--shadow-lg); padding:24px; position:relative;">
        <button onclick="closeCallModal()" style="position:absolute; right:18px; top:18px; background:transparent; border:none; cursor:pointer; color:var(--text-muted);">
            <svg viewBox="0 0 24 24" width="22" height="22"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div id="modalLoading" style="text-align:center; padding:40px 0; color:var(--text-muted);">
            <p>Loading customer profile and call history...</p>
        </div>

        <div id="modalContent" style="display:none;">
            <!-- Customer info header -->
            <div style="border-bottom:1px solid var(--border); padding-bottom:16px; margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                    <div>
                        <h2 id="mName" style="font-size:18px; font-weight:700; margin-bottom:4px; color:var(--text);">Customer Name</h2>
                        <div style="display:flex; gap:12px; flex-wrap:wrap; font-size:13px; color:var(--text-muted);">
                            <span id="mLocation">Location</span>
                            &middot;
                            <span id="mJoined">Joined</span>
                            &middot;
                            <span id="mOrders">0 orders</span>
                        </div>
                    </div>
                    <a id="mPhoneCallBtn" href="tel:" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                        <svg viewBox="0 0 24 24" width="16" height="16"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.92-8.39A2 2 0 0 1 3.46 2h3a2 2 0 0 1 2 1.72"/></svg>
                        <span id="mPhone">Phone</span>
                    </a>
                </div>
            </div>

            <!-- Call Log Form -->
            <form id="callForm" onsubmit="submitCall(event)">
                <input type="hidden" id="mCustomerId" name="customer_id">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label" for="mOutcome">Call Outcome *</label>
                        <select id="mOutcome" name="outcome" class="form-select" required>
                            <option value="no_answer">No answer</option>
                            <option value="interested">Interested</option>
                            <option value="not_ready">Not ready</option>
                            <option value="callback">Callback</option>
                            <option value="ordered_unverified">Order unverified (Says ordered)</option>
                            <option value="do_not_contact">Do not contact</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="mBarrier">Main Barrier</label>
                        <select id="mBarrier" name="barrier" class="form-select">
                            <option value="">Not identified</option>
                            <option value="browsing">Just browsing</option>
                            <option value="trust">Trust</option>
                            <option value="price">Price</option>
                            <option value="app">App difficulty</option>
                            <option value="product">Product unavailable</option>
                            <option value="later">Buying later</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label class="form-label" for="mFollowUp">Next Follow-up Date</label>
                        <input type="date" id="mFollowUp" name="follow_up_on" class="form-input">
                    </div>
                    <div style="display:flex; align-items:center; padding-top:24px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; font-weight:500;">
                            <input type="checkbox" id="mCoupon" name="coupon" value="1" style="width:18px; height:18px;">
                            <span>Coupon code offered</span>
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label" for="mNotes">Notes</label>
                    <textarea id="mNotes" name="notes" class="form-input" rows="3" placeholder="Customer reaction, product requested, callback notes..." maxlength="1500"></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
                    <button type="button" class="btn btn-neutral" onclick="closeCallModal()">Cancel</button>
                    <button type="submit" id="callSubmitBtn" class="btn btn-primary">Save Call Result</button>
                </div>
            </form>

            @if(auth()->user()->isManager())
            <!-- Manager Controls Section -->
            <div style="border-top:2px dashed var(--border); padding-top:18px; margin-top:20px; background:var(--bg); border-radius:var(--radius); padding:16px;">
                <h3 style="font-size:15px; font-weight:700; color:var(--text); margin-bottom:14px; display:flex; align-items:center; gap:6px;">
                    <svg viewBox="0 0 24 24" width="16" height="16" style="stroke:var(--primary);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Manager Controls
                </h3>

                <!-- Reassign contractor -->
                <div style="display:flex; gap:10px; align-items:flex-end; margin-bottom:14px;">
                    <div style="flex:1;">
                        <label class="form-label" for="mReassign">Assigned Contractor</label>
                        <select id="mReassign" class="form-select">
                            @foreach($contractors as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->coupon_code ?: 'No coupon' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" onclick="submitReassign()" class="btn btn-secondary" style="height:42px;">Reassign</button>
                </div>

                <!-- Verify First Order -->
                <form id="verifyForm" onsubmit="submitVerify(event)" style="display:flex; flex-direction:column; gap:12px;">
                    <div>
                        <label class="form-label" for="mVerifiedOrder">Verified First Order ID (from Gojo Admin)</label>
                        <input type="text" id="mVerifiedOrder" class="form-input" placeholder="e.g. ORD-98421">
                    </div>
                    <div style="display:flex; gap:20px; flex-wrap:wrap;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:500;">
                            <input type="checkbox" id="mRedeemed" value="1" style="width:16px; height:16px;">
                            <span>Coupon redeemed</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:500;">
                            <input type="checkbox" id="mDelivered" value="1" style="width:16px; height:16px;">
                            <span>Order delivered</span>
                        </label>
                    </div>
                    <div>
                        <button type="submit" id="verifySubmitBtn" class="btn btn-success" style="width:100%;">Save Verification</button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Call History Timeline -->
            <div style="border-top:1px solid var(--border); padding-top:18px; margin-top:22px;">
                <h3 style="font-size:15px; font-weight:700; margin-bottom:12px; color:var(--text);">Call History</h3>
                <div id="mHistoryList" style="display:flex; flex-direction:column; gap:10px; max-height:220px; overflow-y:auto;">
                    <p style="color:var(--text-muted); font-size:13px;">No prior calls logged.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentCustomerId = null;

async function openCallModal(customerId) {
    currentCustomerId = customerId;
    const modal = document.getElementById('callModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');

    modal.style.display = 'flex';
    loading.style.display = 'block';
    content.style.display = 'none';

    try {
        const res = await fetch(`/customers/${customerId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        const c = data.customer;
        const logs = data.call_logs || [];

        document.getElementById('mCustomerId').value = c.id;
        document.getElementById('mName').textContent = c.name || 'Unnamed Customer';
        document.getElementById('mLocation').textContent = c.location || 'Location not specified';
        document.getElementById('mJoined').textContent = c.joined_on ? 'Joined ' + c.joined_on.slice(0,10) : 'Joined date unknown';
        document.getElementById('mOrders').textContent = (c.total_orders || 0) + ' total orders';
        document.getElementById('mPhone').textContent = c.phone;
        document.getElementById('mPhoneCallBtn').href = 'tel:' + c.phone;

        // Form prefill
        document.getElementById('mOutcome').value = c.activation_status === 'not_called' ? 'no_answer' : (c.activation_status || 'no_answer');
        document.getElementById('mBarrier').value = c.barrier || '';
        document.getElementById('mFollowUp').value = c.follow_up_on ? c.follow_up_on.slice(0,10) : '';
        document.getElementById('mCoupon').checked = !!c.coupon_offered;
        document.getElementById('mNotes').value = '';

        // Manager fields
        const reassignSelect = document.getElementById('mReassign');
        if (reassignSelect && c.assigned_to) {
            reassignSelect.value = c.assigned_to;
        }
        const orderInput = document.getElementById('mVerifiedOrder');
        if (orderInput) orderInput.value = c.verified_order_id || '';
        const redInput = document.getElementById('mRedeemed');
        if (redInput) redInput.checked = !!c.coupon_redeemed;
        const delInput = document.getElementById('mDelivered');
        if (delInput) delInput.checked = !!c.delivered;

        // Render history
        const historyContainer = document.getElementById('mHistoryList');
        if (logs.length === 0) {
            historyContainer.innerHTML = '<p style="color:var(--text-muted); font-size:13px;">No prior calls logged.</p>';
        } else {
            historyContainer.innerHTML = logs.map(l => `
                <div style="background:var(--bg); border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px 14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; font-size:12px;">
                        <span style="font-weight:600; color:var(--text);">${esc(l.contractor ? l.contractor.name : 'Contractor')}</span>
                        <span style="color:var(--text-muted);">${new Date(l.created_at).toLocaleString()}</span>
                    </div>
                    <div style="display:flex; gap:6px; align-items:center; margin-bottom:4px;">
                        <span class="badge badge-neutral" style="font-size:11px;">${esc(l.outcome)}</span>
                        ${l.barrier ? `<span class="badge badge-warning" style="font-size:11px;">Barrier: ${esc(l.barrier)}</span>` : ''}
                        ${l.coupon_offered ? `<span class="badge badge-info" style="font-size:11px;">Coupon</span>` : ''}
                        ${l.follow_up_on ? `<span style="font-size:11px; color:var(--text-muted);">Follow-up: ${l.follow_up_on}</span>` : ''}
                    </div>
                    ${l.notes ? `<div style="font-size:13px; color:var(--text); margin-top:4px;">${esc(l.notes)}</div>` : ''}
                </div>
            `).join('');
        }

        loading.style.display = 'none';
        content.style.display = 'block';
    } catch (e) {
        alert('Failed to load customer: ' + e.message);
        closeCallModal();
    }
}

function closeCallModal() {
    document.getElementById('callModal').style.display = 'none';
}

async function submitCall(e) {
    e.preventDefault();
    const btn = document.getElementById('callSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const payload = {
        outcome: document.getElementById('mOutcome').value,
        barrier: document.getElementById('mBarrier').value,
        follow_up_on: document.getElementById('mFollowUp').value || null,
        coupon: document.getElementById('mCoupon').checked ? 1 : 0,
        notes: document.getElementById('mNotes').value
    };

    try {
        const res = await fetch(`/customers/${currentCustomerId}/call`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error saving call');
        closeCallModal();
        location.reload();
    } catch (err) {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Save Call Result';
    }
}

async function submitReassign() {
    const assignee = document.getElementById('mReassign').value;
    if (!confirm('Reassign customer to this contractor?')) return;

    try {
        const res = await fetch(`/customers/${currentCustomerId}/reassign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ assigned_to: assignee })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error reassigning');
        alert('Customer reassigned.');
        location.reload();
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function submitVerify(e) {
    e.preventDefault();
    const btn = document.getElementById('verifySubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const payload = {
        verified_order_id: document.getElementById('mVerifiedOrder').value,
        coupon_redeemed: document.getElementById('mRedeemed').checked ? 1 : 0,
        delivered: document.getElementById('mDelivered').checked ? 1 : 0
    };

    try {
        const res = await fetch(`/customers/${currentCustomerId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error saving verification');
        alert('Order verification saved.');
        location.reload();
    } catch (err) {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Save Verification';
    }
}

function esc(str) {
    return String(str || '').replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
}
</script>
@endpush
@endsection
