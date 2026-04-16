@extends('adminlte::page')

@section('title', 'Income | PosSales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 style="font-size:1.3rem; font-weight:600; color:#000000;">
        <i class="fas fa-chart-line mr-2" style="color:#000000;"></i> Income
    </h1>
    <div>
        <button onclick="window.printIncome()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333; margin-right:10px;">
            <i class="fas fa-print mr-1"></i> Print
        </button>
        <button onclick="window.exportToCSV()" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background:#ffffff; color:#000000; border:2px solid #333333;">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        {{-- Summary Cards --}}
        <div style="display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Income</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ number_format($dailyIncomes->sum('amount'), 2) }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total Records</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ $dailyIncomes->count() }}</div>
            </div>
            <div style="flex:1; min-width:160px; background:#ffffff; border:2px solid #333333; border-radius:4px; padding:16px;">
                <div style="font-size:12px; color:#666666; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Average Daily</div>
                <div style="font-size:28px; font-weight:700; color:#000000; margin-top:4px;">{{ number_format($dailyIncomes->avg('amount'), 2) }}</div>
            </div>
        </div>

        <div class="card" style="background:#ffffff; border:2px solid #333333; border-radius:4px;">
            <div class="card-header" style="background:#f5f5f5; border-bottom:2px solid #333333; padding:16px;">
                <h5 style="margin:0; font-weight:600; color:#000000;">
                    <i class="fas fa-chart-line mr-2" style="color:#000000;"></i> Income Records
                </h5>
            </div>
            <div class="card-body" style="padding:20px;">
                <div class="table-responsive" style="border:2px solid #333333; border-radius:4px; overflow:hidden;">
                    <table class="table" style="margin:0; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f5f5f5; border-bottom:2px solid #333333;">
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">SL.</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Date</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Description</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Type</th>
                                <th style="padding:12px; font-weight:600; color:#000000; border-right:1px solid #333333;">Amount</th>
                                <th style="padding:12px; font-weight:600; color:#000000;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyIncomes as $income)
                            <tr>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $loop->index + 1 }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ date('d/m/Y', strtotime($income['date'])) }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $income['description'] }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ $income['type'] }}</td>
                                <td style="padding:12px; border-right:1px solid #333333; border-bottom:1px solid #333333;">{{ number_format($income['amount'], 2) }}</td>
                                <td style="padding:12px; border-bottom:1px solid #333333;">
                                    <div class="btn-group btn-group-sm" style="gap:4px;">
                                        <button onclick="editIncome({{ $income['id'] }})" class="btn btn-sm" title="Edit" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:6px 10px;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteIncome({{ $income['id'] }})" class="btn btn-sm" title="Delete" style="background:#ffffff; color:#cc0000; border:2px solid #cc0000; padding:6px 10px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color:#000000;">
                                    <i class="fas fa-chart-line fa-3x mb-3" style="display:block; opacity:0.3; color:#000000;"></i>
                                    <h5 style="color:#000000;">No income records found</h5>
                                    <p style="color:#666666;">Start adding income records to see them here.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

{{-- Edit Modal --}}
<div id="editModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:#ffffff; margin:10% auto; padding:20px; border:2px solid #333333; width:500px; border-radius:4px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; color:#000000; font-size:1.2rem; font-weight:600;">
                <i class="fas fa-edit mr-2" style="color:#000000;"></i> Edit Income Record
            </h3>
            <button onclick="closeEditModal()" style="background:#ffffff; color:#000000; border:2px solid #333333; padding:5px 10px; cursor:pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="editForm" onsubmit="updateIncome(event)">
            <input type="hidden" id="editId" name="id">

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; color:#000000; font-weight:600;">Date</label>
                <input type="date" id="editDate" name="date" required
                       style="width:100%; padding:8px; border:2px solid #333333; border-radius:3px; color:#000000; background:#ffffff;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; color:#000000; font-weight:600;">Description</label>
                <input type="text" id="editDescription" name="description" required
                       style="width:100%; padding:8px; border:2px solid #333333; border-radius:3px; color:#000000; background:#ffffff;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; color:#000000; font-weight:600;">Type</label>
                <select id="editType" name="type" required
                        style="width:100%; padding:8px; border:2px solid #333333; border-radius:3px; color:#000000; background:#ffffff;">
                    <option value="">Select Type</option>
                    <option value="Sales">Sales</option>
                    <option value="Service">Service</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; color:#000000; font-weight:600;">Amount</label>
                <input type="number" id="editAmount" name="amount" step="0.01" required
                       style="width:100%; padding:8px; border:2px solid #333333; border-radius:3px; color:#000000; background:#ffffff;">
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeEditModal()"
                        style="padding:8px 16px; background:#ffffff; color:#000000; border:2px solid #333333; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:8px 16px; background:#000000; color:#ffffff; border:2px solid #000000; cursor:pointer;">
                    Update Income
                </button>
            </div>
        </form>
    </div>
</div>

@section('adminlte_js')
<script>
// Make functions globally available
window.exportToCSV = function() {
    try {
        const incomes = @json($dailyIncomes);

        if (!incomes || incomes.length === 0) {
            alert('No data available to export');
            return;
        }

        let csv = 'SL.,Date,Description,Type,Amount\n';

        incomes.forEach((income, index) => {
            const sl = index + 1;
            const date = income.date || '';
            const description = (income.description || '').replace(/"/g, '""');
            const type = (income.type || '').replace(/"/g, '""');
            const amount = income.amount || 0;

            csv += `${sl},"${date}","${description}","${type}","${amount}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'income_report_' + new Date().toISOString().slice(0,10) + '.csv';
        a.style.display = 'none';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        console.log('CSV export completed');
    } catch (error) {
        console.error('CSV export error:', error);
        alert('Error exporting CSV: ' + error.message);
    }
};

window.printIncome = function() {
    try {
        console.log('Print function called');
        window.print();
    } catch (error) {
        console.error('Print error:', error);
        alert('Error printing: ' + error.message);
    }
};

// Debug: Check if data is available
console.log('Income data:', @json($dailyIncomes));

window.editIncome = function(id) {
    const incomes = @json($dailyIncomes);
    const income = incomes.find(item => item.id == id);

    if (income) {
        // Populate form fields
        document.getElementById('editId').value = income.id;
        document.getElementById('editDate').value = income.date;
        document.getElementById('editDescription').value = income.description;
        document.getElementById('editType').value = income.type;
        document.getElementById('editAmount').value = income.amount;

        // Show modal
        document.getElementById('editModal').style.display = 'block';
    }
};

window.closeEditModal = function() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
};

window.updateIncome = function(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById('editForm'));
    const data = Object.fromEntries(formData);

    // For now, just show success message - you can implement actual update logic later
    alert('Income record updated successfully!\n\nID: ' + data.id + '\nDate: ' + data.date + '\nDescription: ' + data.description + '\nType: ' + data.type + '\nAmount: ' + data.amount);

    closeEditModal();
};

window.deleteIncome = function(id) {
    if (confirm('Are you sure you want to delete this income record?\n\nThis action cannot be undone.')) {
        // For now, just show an alert - you can implement actual delete logic later
        alert('Delete functionality for income ID: ' + id + '\n\nThis would send a delete request to the server and remove the record.');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Income page loaded');
    console.log('Functions available:', typeof window.exportToCSV, typeof window.printIncome, typeof window.editIncome, typeof window.deleteIncome);

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeEditModal();
        }
    };
});
</script>
@stop

@section('adminlte_css')
<style>
body { background: #ffffff !important; }
.table tbody tr:hover { background: #f5f5f5 !important; }
.btn:hover { background: #f5f5f5 !important; border-color: #000000 !important; }
.form-control:focus { border-color: #000000 !important; box-shadow: none !important; }

/* Modal Styles */
.modal {
    font-family: Arial, sans-serif;
}

.modal input:focus, .modal select:focus {
    border-color: #000000 !important;
    box-shadow: none !important;
    outline: none;
}

.modal button:hover {
    opacity: 0.8;
}
@media print {
    body * { visibility: hidden; }
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .table-responsive { display: block !important; }
    .card { display: block !important; }
    .card-header { display: block !important; }
    .card-body { display: block !important; }
    table { display: block !important; }
    thead { display: table-header-group !important; }
    tbody { display: table-row-group !important; }
    tr { display: table-row !important; }
    th, td {
        display: table-cell !important;
        border: 1px solid #000 !important;
        padding: 8px !important;
        vertical-align: top !important;
    }
    th {
        background: #f5f5f5 !important;
        font-weight: bold !important;
        color: #000 !important;
    }
}
</style>
@stop
