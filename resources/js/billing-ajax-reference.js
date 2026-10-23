/**
 * Hospital Billing System - AJAX Reference Guide
 * File: resources/js/billing-ajax-reference.js
 *
 * This file contains example AJAX calls for the billing system
 * All calls use jQuery and include proper error handling
 */

// ============================================================================
// CONFIGURATION
// ============================================================================

const BillingAPI = {
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    baseURL: '/invoice',

    /**
     * Initialize CSRF token from meta tag
     */
    init: function() {
        if (!this.csrfToken) {
            console.error('CSRF token not found. Add to layout: <meta name="csrf-token" content="{{ csrf_token() }}">');
        }
    },

    /**
     * Common AJAX request helper
     */
    request: function(method, endpoint, data = {}) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: this.baseURL + endpoint,
                type: method,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify(data),
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject({
                        status: xhr.status,
                        message: xhr.responseJSON?.message || error,
                        errors: xhr.responseJSON?.errors || {}
                    });
                }
            });
        });
    }
};

// ============================================================================
// 1. GET ADMISSION TYPES (Main Categories)
// ============================================================================

/**
 * Fetch all admission types
 */
async function getAdmissionTypes() {
    try {
        const response = await BillingAPI.request('POST', '/get-main');

        if (response.success) {
            console.log('Admission Types:', response.data);

            // Populate dropdown
            const select = document.getElementById('mainCategory');
            response.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.code;
                option.text = category.name;
                select.appendChild(option);
            });

            return response.data;
        }
    } catch (error) {
        console.error('Failed to load admission types:', error);
    }
}

// ============================================================================
// 2. GET INVESTIGATIONS (Sub Categories)
// ============================================================================

/**
 * Fetch investigations for a selected admission type
 * @param {string} mainCode - The admission type code
 */
async function getInvestigations(mainCode) {
    if (!mainCode) {
        console.warn('Main code is required');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/get-sub', {
            main_code: mainCode
        });

        if (response.success) {
            console.log('Investigations:', response.data);

            // Display as checkboxes
            displayInvestigationCheckboxes(response.data);
            return response.data;
        } else {
            console.error('Failed to load investigations');
        }
    } catch (error) {
        console.error('Error fetching investigations:', error);
    }
}

/**
 * Helper: Display investigations as checkboxes
 */
function displayInvestigationCheckboxes(investigations) {
    const container = document.getElementById('subCategoriesList');
    let html = '';

    investigations.forEach(inv => {
        html += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       value="${inv.code}"
                       id="inv_${inv.code}"
                       data-name="${inv.name}"
                       data-amount="${inv.amount}">
                <label class="form-check-label" for="inv_${inv.code}">
                    <strong>${inv.name}</strong>
                    <span class="float-right badge badge-primary">
                        $${parseFloat(inv.amount).toFixed(2)}
                    </span>
                </label>
            </div>
        `;
    });

    container.innerHTML = html;
}

// ============================================================================
// 3. SEARCH PATIENTS
// ============================================================================

/**
 * Search for patients by code or name
 * @param {string} query - Search term
 */
async function searchPatients(query) {
    if (!query || query.length < 1) {
        console.warn('Search query is required');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/search-patients', {
            q: query
        });

        if (response.success) {
            console.log('Found patients:', response.data);
            displayPatientSearchResults(response.data);
            return response.data;
        }
    } catch (error) {
        console.error('Patient search error:', error);
    }
}

/**
 * Helper: Display patient search results as dropdown list
 */
function displayPatientSearchResults(patients) {
    const container = document.getElementById('patientSearchResults');
    let html = '';

    if (patients.length === 0) {
        html = '<div class="list-group-item text-muted">No patients found</div>';
    } else {
        patients.forEach(patient => {
            html += `
                <a href="#" class="list-group-item list-group-item-action"
                   onclick="selectPatient('${patient.code}'); return false;">
                    <strong>${patient.name}</strong><br>
                    <small class="text-muted">Code: ${patient.code}</small>
                </a>
            `;
        });
    }

    container.innerHTML = html;
    container.style.display = 'block';
}

// ============================================================================
// 4. GET PATIENT DETAILS
// ============================================================================

/**
 * Get detailed information about a patient
 * @param {string} patientCode - Patient code
 */
async function getPatientDetails(patientCode) {
    if (!patientCode) {
        console.warn('Patient code is required');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/get-patient', {
            patient_code: patientCode
        });

        if (response.success) {
            console.log('Patient Details:', response.data);

            // Display patient info
            document.getElementById('patientName').textContent = response.data.name;
            document.getElementById('patientCode').textContent = response.data.code;
            document.getElementById('patientContact').textContent =
                response.data.phone || response.data.email || 'N/A';

            document.getElementById('patientDetailsContainer').style.display = 'block';

            return response.data;
        }
    } catch (error) {
        console.error('Error fetching patient details:', error);
    }
}

// ============================================================================
// 5. ADD INVESTIGATIONS TO TEMPORARY TABLE
// ============================================================================

/**
 * Add selected investigations to temporary bill
 * @param {string} mainCode - Admission type code
 * @param {string} patientCode - Patient code
 * @param {array} subCodes - Array of investigation codes
 */
async function addInvestigations(mainCode, patientCode, subCodes) {
    if (!mainCode || !patientCode || !subCodes || subCodes.length === 0) {
        console.warn('Missing required parameters');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/add-tmp', {
            main_code: mainCode,
            patient_code: patientCode,
            sub_codes: subCodes
        });

        if (response.success) {
            console.log('Added investigations:', response.message);

            // Refresh temporary items
            loadTemporaryItems(patientCode);

            return response;
        } else {
            console.error('Failed to add investigations:', response.message);
        }
    } catch (error) {
        console.error('Error adding investigations:', error);
    }
}

// ============================================================================
// 6. GET TEMPORARY BILL ITEMS
// ============================================================================

/**
 * Fetch temporary bill items for a patient
 * @param {string} patientCode - Patient code
 */
async function loadTemporaryItems(patientCode) {
    if (!patientCode) {
        console.warn('Patient code is required');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/get-tmp', {
            patient_code: patientCode
        });

        if (response.success) {
            console.log('Temporary Items:', response.data);
            console.log('Total Amount:', response.total);

            // Display items and update total
            displayTemporaryItems(response.data);
            updateTotalAmount(response.total);

            return response;
        }
    } catch (error) {
        console.error('Error loading temporary items:', error);
    }
}

/**
 * Helper: Display temporary items
 */
function displayTemporaryItems(items) {
    const container = document.getElementById('selectedItemsList');

    if (items.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No items selected yet</p>';
        return;
    }

    let html = '<ul class="list-group list-group-flush">';
    items.forEach(item => {
        html += `
            <li class="list-group-item d-flex justify-content-between">
                <div>
                    <strong>${item.name}</strong><br>
                    <small class="text-muted">$${parseFloat(item.amount).toFixed(2)}</small>
                </div>
                <button class="btn btn-sm btn-danger" onclick="removeItem(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </li>
        `;
    });
    html += '</ul>';

    container.innerHTML = html;
}

/**
 * Helper: Update total amount
 */
function updateTotalAmount(total) {
    document.getElementById('totalAmount').value = parseFloat(total).toFixed(2);
    calculateDue();
}

// ============================================================================
// 7. REMOVE ITEM FROM TEMPORARY TABLE
// ============================================================================

/**
 * Remove an item from temporary bill
 * @param {number} itemId - Temporary item ID
 */
async function removeItem(itemId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/remove-tmp', {
            id: itemId
        });

        if (response.success) {
            console.log('Item removed:', response.message);

            // Refresh items
            const patientCode = document.getElementById('patientSearch').dataset.patientCode;
            if (patientCode) {
                loadTemporaryItems(patientCode);
            }

            return response;
        }
    } catch (error) {
        console.error('Error removing item:', error);
    }
}

// ============================================================================
// 8. AMOUNT CALCULATION
// ============================================================================

/**
 * Calculate due amount (Total - Paid)
 */
function calculateDue() {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
    const due = total - paid;

    console.log(`Total: $${total}, Paid: $${paid}, Due: $${due}`);

    document.getElementById('dueAmount').textContent = due.toFixed(2);

    // Show/hide due amount container
    const container = document.getElementById('dueAmountContainer');
    if (due > 0) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }

    return due;
}

// ============================================================================
// 9. CREATE INVOICE
// ============================================================================

/**
 * Generate final invoice
 * @param {string} patientCode - Patient code
 * @param {number} paidAmount - Amount paid
 */
async function generateInvoice(patientCode, paidAmount) {
    if (!patientCode) {
        console.warn('Patient code is required');
        return;
    }

    if (isNaN(paidAmount) || paidAmount < 0) {
        console.warn('Invalid paid amount');
        return;
    }

    try {
        const response = await BillingAPI.request('POST', '/store', {
            patient_code: patientCode,
            paid_amount: parseFloat(paidAmount)
        });

        if (response.success) {
            console.log('Invoice generated:', response);

            // Display success information
            showInvoiceSuccess({
                billNo: response.bill_no,
                total: response.total,
                paid: response.paid,
                due: response.due
            });

            return response;
        } else {
            console.error('Failed to generate invoice:', response.message);
        }
    } catch (error) {
        console.error('Error generating invoice:', error);
    }
}

/**
 * Helper: Show invoice success message
 */
function showInvoiceSuccess(invoiceData) {
    console.log('Invoice Success:', invoiceData);

    // Update modal with invoice details
    document.getElementById('billNumberDisplay').textContent = invoiceData.billNo;
    document.getElementById('totalDisplay').textContent = '$' + parseFloat(invoiceData.total).toFixed(2);
    document.getElementById('paidDisplay').textContent = '$' + parseFloat(invoiceData.paid).toFixed(2);
    document.getElementById('dueDisplay').textContent = '$' + parseFloat(invoiceData.due).toFixed(2);

    // Show print link
    document.getElementById('printInvoiceBtn').href = '/invoice/print/' + invoiceData.billNo;

    // Display success modal
    $('#successModal').modal('show');
}

// ============================================================================
// 10. PRINT INVOICE
// ============================================================================

/**
 * Open invoice for printing
 * @param {string} billNo - Bill number
 */
function printInvoice(billNo) {
    window.open(`/invoice/print/${billNo}`, '_blank');
}

/**
 * Print current page (from invoice print view)
 */
function printCurrentPage() {
    window.print();
}

// ============================================================================
// EVENT LISTENERS SETUP
// ============================================================================

/**
 * Setup all event listeners
 */
function setupBillingEventListeners() {
    // Admission type change
    document.getElementById('mainCategory')?.addEventListener('change', function(e) {
        if (this.value) {
            getInvestigations(this.value);
        }
    });

    // Patient search
    document.getElementById('patientSearch')?.addEventListener('keyup', function(e) {
        if (this.value.length > 0) {
            searchPatients(this.value);
        }
    });

    // Paid amount change
    document.getElementById('paidAmount')?.addEventListener('change', calculateDue);

    // Add investigations button
    document.getElementById('addInvestigationsBtn')?.addEventListener('click', function(e) {
        const mainCode = document.getElementById('mainCategory').value;
        const patientCode = document.getElementById('patientSearch').dataset.patientCode;
        const selectedSubs = Array.from(
            document.querySelectorAll('input[class="form-check-input"]:checked')
        ).map(el => el.value);

        if (patientCode && selectedSubs.length > 0) {
            addInvestigations(mainCode, patientCode, selectedSubs);
        }
    });

    // Generate invoice button
    document.getElementById('generateInvoiceBtn')?.addEventListener('click', function(e) {
        const patientCode = document.getElementById('patientSearch').dataset.patientCode;
        const paidAmount = document.getElementById('paidAmount').value;

        if (patientCode) {
            generateInvoice(patientCode, paidAmount);
        }
    });
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Initialize when page loads
$(document).ready(function() {
    BillingAPI.init();
    setupBillingEventListeners();

    // Load admission types
    getAdmissionTypes();
});

// ============================================================================
// USAGE EXAMPLES
// ============================================================================

/*

// 1. Get admission types
getAdmissionTypes().then(types => {
    console.log('Available types:', types);
});

// 2. Get investigations for a type
getInvestigations('LAB').then(investigations => {
    console.log('Lab investigations:', investigations);
});

// 3. Search patients
searchPatients('John').then(patients => {
    console.log('Search results:', patients);
});

// 4. Get patient details
getPatientDetails('PAT001').then(patient => {
    console.log('Patient info:', patient);
});

// 5. Add investigations to temp bill
addInvestigations('LAB', 'PAT001', ['CB', 'BS']).then(response => {
    console.log('Added:', response.added, 'Duplicates:', response.duplicates);
});

// 6. Load temp items
loadTemporaryItems('PAT001').then(response => {
    console.log('Total items:', response.count, 'Amount:', response.total);
});

// 7. Generate invoice
generateInvoice('PAT001', 100).then(invoice => {
    console.log('Invoice number:', invoice.bill_no);
});

// 8. Print invoice
printInvoice('BIL-20240420123456-7890');

*/

// ============================================================================
// ERROR HANDLING UTILITIES
// ============================================================================

/**
 * Handle API error responses
 */
function handleAPIError(error) {
    console.error('API Error:', error);

    if (error.status === 422) {
        // Validation errors
        Object.entries(error.errors).forEach(([field, messages]) => {
            console.error(`${field}:`, messages.join(', '));
        });
    } else if (error.status === 404) {
        console.error('Resource not found:', error.message);
    } else if (error.status === 500) {
        console.error('Server error:', error.message);
    }
}

// ============================================================================
// END OF FILE
// ============================================================================
