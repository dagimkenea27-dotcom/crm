@extends('layouts.app')

@section('title', 'Import Customers')

@section('content')
<div class="page-header">
    <h1 class="page-title">Import Customers</h1>
    <p class="page-subtitle">Batch upload customer leads from Excel or CSV and assign them to remote contractors</p>
</div>

<!-- Multi-step Import Container -->
<div class="card" style="max-width:860px; margin:0 auto; padding:28px 32px;">
    <!-- Step Indicators -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; position:relative;">
        <div style="position:absolute; top:18px; left:40px; right:40px; height:2px; background:var(--border); z-index:1;"></div>
        
        <div style="display:flex; flex-direction:column; align-items:center; position:relative; z-index:2;">
            <div id="stepNum1" style="width:36px; height:36px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; box-shadow:0 0 0 4px var(--primary-light);">1</div>
            <span style="font-size:13px; font-weight:600; color:var(--text); margin-top:8px;">Upload File</span>
        </div>

        <div style="display:flex; flex-direction:column; align-items:center; position:relative; z-index:2;">
            <div id="stepNum2" style="width:36px; height:36px; border-radius:50%; background:var(--border); color:var(--text-muted); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;">2</div>
            <span style="font-size:13px; font-weight:600; color:var(--text-muted); margin-top:8px;">Assign Contractor</span>
        </div>

        <div style="display:flex; flex-direction:column; align-items:center; position:relative; z-index:2;">
            <div id="stepNum3" style="width:36px; height:36px; border-radius:50%; background:var(--border); color:var(--text-muted); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;">3</div>
            <span style="font-size:13px; font-weight:600; color:var(--text-muted); margin-top:8px;">Review & Import</span>
        </div>
    </div>

    <!-- Step 1: Upload File -->
    <div id="step1Section">
        <div id="dropZone" style="border:2px dashed var(--border); border-radius:var(--radius-lg); padding:44px 20px; text-align:center; background:var(--bg); cursor:pointer; transition:border-color 0.2s;"
             onclick="document.getElementById('fileInput').click()"
             ondragover="event.preventDefault(); this.style.borderColor='var(--primary)';"
             ondragleave="this.style.borderColor='var(--border)';"
             ondrop="handleDrop(event)">
            <svg viewBox="0 0 24 24" width="48" height="48" style="stroke:var(--primary); margin:0 auto 12px; display:block;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div style="font-size:16px; font-weight:600; color:var(--text); margin-bottom:6px;">
                Choose an Excel or CSV file or drag it here
            </div>
            <p style="font-size:13px; color:var(--text-muted); margin:0 0 16px;">Supports .xlsx, .xls, and .csv files up to 10MB</p>
            <button type="button" class="btn btn-secondary">Browse Files</button>
            <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display:none;" onchange="handleFileSelect(this.files[0])">
        </div>

        <div id="fileInfoCard" style="display:none; margin-top:16px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:12px;">
                <svg viewBox="0 0 24 24" width="24" height="24" style="stroke:var(--success);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <div>
                    <div id="selectedFileName" style="font-weight:600; font-size:14px; color:var(--text);">file.xlsx</div>
                    <div id="selectedFileSize" style="font-size:12px; color:var(--text-muted);">0 KB</div>
                </div>
            </div>
            <button class="btn btn-sm btn-neutral" onclick="resetFile()">Change</button>
        </div>

        <div style="margin-top:24px; display:flex; justify-content:flex-end;">
            <button type="button" id="previewBtn" class="btn btn-primary" disabled onclick="uploadAndPreview()">
                Continue to Contractor Assignment &rarr;
            </button>
        </div>
    </div>

    <!-- Step 2: Assign Contractor -->
    <div id="step2Section" style="display:none;">
        <div style="margin-bottom:20px;">
            <label class="form-label" style="font-size:14px; font-weight:600; margin-bottom:8px;">Select Contractor for this Batch *</label>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">All valid new leads from this import file will be automatically assigned to the chosen contractor.</p>
            <select id="assigneeSelect" class="form-select" style="max-width:400px; font-size:14px; padding:12px;">
                <option value="">-- Choose Contractor --</option>
                @foreach($contractors as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} (Coupon: {{ $c->coupon_code ?: 'None' }})</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; justify-content:space-between; margin-top:28px;">
            <button type="button" class="btn btn-neutral" onclick="goToStep(1)">&larr; Back</button>
            <button type="button" id="toStep3Btn" class="btn btn-primary" onclick="goToStep(3)">
                Review Import Preview &rarr;
            </button>
        </div>
    </div>

    <!-- Step 3: Review & Finalize -->
    <div id="step3Section" style="display:none;">
        <div id="previewStatsGrid" style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:20px;">
            <div style="background:var(--bg); padding:14px; border-radius:var(--radius); border:1px solid var(--border); text-align:center;">
                <div id="statTotal" style="font-size:22px; font-weight:700; color:var(--text);">0</div>
                <div style="font-size:12px; color:var(--text-muted);">Total Rows</div>
            </div>
            <div style="background:var(--success-bg); padding:14px; border-radius:var(--radius); border:1px solid rgba(5,150,105,0.2); text-align:center;">
                <div id="statValid" style="font-size:22px; font-weight:700; color:var(--success);">0</div>
                <div style="font-size:12px; color:var(--success);">Valid Unique</div>
            </div>
            <div style="background:var(--warning-bg); padding:14px; border-radius:var(--radius); border:1px solid rgba(217,119,6,0.2); text-align:center;">
                <div id="statDupes" style="font-size:22px; font-weight:700; color:var(--warning);">0</div>
                <div style="font-size:12px; color:var(--warning);">Duplicates in File</div>
            </div>
            <div style="background:var(--danger-bg); padding:14px; border-radius:var(--radius); border:1px solid rgba(220,38,38,0.2); text-align:center;">
                <div id="statInvalid" style="font-size:22px; font-weight:700; color:var(--danger);">0</div>
                <div style="font-size:12px; color:var(--danger);">Invalid Phones</div>
            </div>
        </div>

        <div style="background:#f8fafc; border:1px solid var(--border); border-radius:var(--radius); padding:12px 16px; margin-bottom:18px; font-size:13px; color:var(--text-muted);">
            <svg viewBox="0 0 24 24" width="16" height="16" style="stroke:var(--primary); display:inline; vertical-align:middle; margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <strong>Note:</strong> Any phone number already registered in the database will be preserved. Existing assignments and call history will <em>not</em> be overwritten.
        </div>

        <h3 style="font-size:14px; font-weight:600; margin-bottom:8px;">First 5 Sample Records:</h3>
        <div class="table-responsive" style="margin-bottom:24px; border:1px solid var(--border); border-radius:var(--radius);">
            <table class="table" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Total Orders</th>
                        <th>Product</th>
                    </tr>
                </thead>
                <tbody id="previewTableBody">
                    <tr><td colspan="5" style="text-align:center;">No data</td></tr>
                </tbody>
            </table>
        </div>

        <div id="importProgress" style="display:none; margin-bottom:18px;">
            <div style="height:6px; background:var(--border); border-radius:3px; overflow:hidden;">
                <div id="importProgressBar" style="height:100%; width:0%; background:var(--primary); transition:width 0.3s;"></div>
            </div>
            <div id="importStatusText" style="font-size:13px; color:var(--text-muted); margin-top:8px; text-align:center;">Importing...</div>
        </div>

        <div style="display:flex; justify-content:space-between;">
            <button type="button" class="btn btn-neutral" onclick="goToStep(2)">&larr; Back</button>
            <button type="button" id="startImportBtn" class="btn btn-primary" onclick="executeImport()">
                Confirm & Import Customers
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedFile = null;
let stagedPreview = null;

function handleFileSelect(file) {
    if (!file) return;
    selectedFile = file;
    document.getElementById('selectedFileName').textContent = file.name;
    document.getElementById('selectedFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('fileInfoCard').style.display = 'flex';
    document.getElementById('previewBtn').disabled = false;
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--border)';
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        handleFileSelect(e.dataTransfer.files[0]);
    }
}

function resetFile() {
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('fileInfoCard').style.display = 'none';
    document.getElementById('previewBtn').disabled = true;
}

function goToStep(step) {
    document.getElementById('step1Section').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('step2Section').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('step3Section').style.display = step === 3 ? 'block' : 'none';

    // Update step circles
    [1, 2, 3].forEach(n => {
        const el = document.getElementById('stepNum' + n);
        if (n <= step) {
            el.style.background = 'var(--primary)';
            el.style.color = 'white';
        } else {
            el.style.background = 'var(--border)';
            el.style.color = 'var(--text-muted)';
        }
    });
}

async function uploadAndPreview() {
    if (!selectedFile) return;

    const btn = document.getElementById('previewBtn');
    btn.disabled = true;
    btn.textContent = 'Parsing File...';

    const formData = new FormData();
    formData.append('file', selectedFile);

    try {
        const res = await fetch('{{ route('import.preview') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Failed to process file');

        stagedPreview = data;
        document.getElementById('statTotal').textContent = data.total;
        document.getElementById('statValid').textContent = data.valid;
        document.getElementById('statDupes').textContent = data.dupes;
        document.getElementById('statInvalid').textContent = data.invalid;

        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = (data.preview || []).map(r => `
            <tr>
                <td style="font-family:monospace; font-weight:600;">${esc(r.phone)}</td>
                <td>${esc(r.name || '—')}</td>
                <td>${esc(r.location || '—')}</td>
                <td>${esc(r.orders || '0')}</td>
                <td>${esc(r.product || '—')}</td>
            </tr>
        `).join('') || '<tr><td colspan="5" style="text-align:center;">No rows parsed</td></tr>';

        btn.disabled = false;
        btn.textContent = 'Continue to Contractor Assignment →';
        goToStep(2);
    } catch (err) {
        alert('File Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Continue to Contractor Assignment →';
    }
}

async function executeImport() {
    const assignee = document.getElementById('assigneeSelect').value;
    if (!assignee) {
        alert('Please go back to Step 2 and choose a contractor.');
        goToStep(2);
        return;
    }

    const btn = document.getElementById('startImportBtn');
    btn.disabled = true;
    btn.textContent = 'Importing...';

    const progress = document.getElementById('importProgress');
    const bar = document.getElementById('importProgressBar');
    const statusText = document.getElementById('importStatusText');
    progress.style.display = 'block';
    bar.style.width = '50%';
    statusText.textContent = 'Inserting valid records and skipping duplicates...';

    try {
        const res = await fetch('{{ route('import.store') }}', {
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
        if (!res.ok) throw new Error(data.error || data.message || 'Import failed');

        bar.style.width = '100%';
        bar.style.background = 'var(--success)';
        statusText.textContent = data.message;
        alert(data.message);
        window.location.href = '{{ route('customers.index') }}';
    } catch (err) {
        alert('Import Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Confirm & Import Customers';
        progress.style.display = 'none';
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
