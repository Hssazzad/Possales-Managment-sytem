@extends('adminlte::page')

@section('title', 'Create Supplier | PosSales')

@section('content_header')
<!-- Government Style Header -->
<div style="background: linear-gradient(to right, #003d5c 0%, #00516d 100%); border-bottom: 4px solid #d4af37; padding: 0;">
    <div style="max-width: 100%; padding: 1.5rem 2rem;">
        <!-- Official Banner -->
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <p style="margin: 0; color: #d4af37; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">SUPPLIER MANAGEMENT SYSTEM</p>
                    <h1 style="margin: 0.5rem 0 0 0; color: white; font-size: 2rem; font-weight: 700; font-family: Georgia, serif;">
                        <i class="fas fa-plus-circle" style="margin-right: 1rem; color: #d4af37;"></i>
                        Register New Supplier
                    </h1>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #d4af37; font-size: 0.8rem; font-weight: 600;">New Registration Form</p>
                    <p style="margin: 0.25rem 0 0 0; color: #a8a8a8; font-size: 0.75rem;">{{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div style="padding-top: 1rem; border-top: 1px solid #00516d; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="{{ route('dashboard') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-home"></i> Home
                </a>
                <span style="color: #666;">
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
                <a href="{{ route('suppliers.index') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-truck"></i> Suppliers
                </a>
                <span style="color: #666;">
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
                <span style="color: #e5e5e5; font-size: 0.85rem;">Create New</span>
            </nav>
            <a href="{{ route('suppliers.index') }}" style="background: #e0e0e0; color: #003d5c; padding: 0.6rem 1.2rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s;"
               onmouseover="this.style.background = '#d0d0d0';"
               onmouseout="this.style.background = '#e0e0e0';">
                <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Suppliers
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div style="background: #f5f5f5; padding: 2rem 0; min-height: calc(100vh - 200px);">
    <div style="max-width: 100%; margin: 0 auto; padding: 0 1rem;">

        <!-- Display Validation Errors -->
        @if ($errors->any())
        <div style="background: #ffebee; border: 1px solid #ef9a9a; border-left: 4px solid #d32f2f; border-radius: 2px; padding: 1.5rem; margin-bottom: 2rem;">
            <h4 style="margin: 0 0 1rem 0; color: #b71c1c; font-size: 0.95rem; font-weight: 700;">
                <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> Validation Errors
            </h4>
            <ul style="margin: 0; padding-left: 1.5rem; color: #c62828;">
                @foreach ($errors->all() as $error)
                    <li style="margin-bottom: 0.5rem; font-size: 0.9rem;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem;">
            <!-- Main Form -->
            <div>
                <!-- Supplier Information Card -->
                <div style="background: white; border: 1px solid #d0d0d0; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                    <div style="background: linear-gradient(to right, #003d5c 0%, #004d70 100%); padding: 1.5rem; border-bottom: 3px solid #d4af37;">
                        <h2 style="margin: 0; color: white; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-truck" style="margin-right: 0.75rem;"></i>
                            Supplier Information
                        </h2>
                    </div>

                    <form id="supplierForm" method="POST" action="{{ route('suppliers.store') }}" style="padding: 2rem;">
                        @csrf

                        <!-- Section 1: Basic Information -->
                        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                                <i class="fas fa-info" style="margin-right: 0.5rem;"></i> Basic Information
                            </h3>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Supplier Name <span style="color: #d32f2f;">*</span>
                                    </label>
                                    <input type="text" name="name" placeholder="Enter supplier name" required
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('name') }}">
                                    @error('name')
                                        <small style="color: #d32f2f; margin-top: 0.25rem; display: block;">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Phone Number <span style="color: #d32f2f;">*</span>
                                    </label>
                                    <input type="tel" name="phone" placeholder="Enter phone number" required
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('phone') }}">
                                    @error('phone')
                                        <small style="color: #d32f2f; margin-top: 0.25rem; display: block;">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Company Name
                                    </label>
                                    <input type="text" name="company_name" placeholder="Enter company name"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('company_name') }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Supplier Type
                                    </label>
                                    <select name="supplier_type"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; cursor: pointer; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';">
                                        <option value="">-- Select Type --</option>
                                        <option value="manufacturer" {{ old('supplier_type') == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                        <option value="distributor" {{ old('supplier_type') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                        <option value="wholesaler" {{ old('supplier_type') == 'wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                                        <option value="retailer" {{ old('supplier_type') == 'retailer' ? 'selected' : '' }}>Retailer</option>
                                        <option value="service" {{ old('supplier_type') == 'service' ? 'selected' : '' }}>Service Provider</option>
                                        <option value="importer" {{ old('supplier_type') == 'importer' ? 'selected' : '' }}>Importer</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact Information -->
                        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                                <i class="fas fa-phone" style="margin-right: 0.5rem;"></i> Contact Information
                            </h3>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Email Address
                                    </label>
                                    <input type="email" name="email" placeholder="Enter email address"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('email') }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Contact Person
                                    </label>
                                    <input type="text" name="contact_person" placeholder="Enter contact person name"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('contact_person') }}">
                                </div>
                            </div>

                            <div>
                                <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                    Address
                                </label>
                                <textarea name="address" rows="3" placeholder="Enter full address"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s; resize: vertical;"
                                    onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                    onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';">{{ old('address') }}</textarea>
                            </div>
                        </div>

                        <!-- Section 3: Location Information -->
                        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                                <i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> Location Details
                            </h3>

                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        City
                                    </label>
                                    <input type="text" name="city" placeholder="Enter city"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('city') }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        State/Province
                                    </label>
                                    <input type="text" name="state" placeholder="Enter state"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('state') }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Zip Code
                                    </label>
                                    <input type="text" name="zip_code" placeholder="Enter zip code"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('zip_code') }}">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Country
                                    </label>
                                    <input type="text" name="country" placeholder="Enter country"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('country') }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Billing Address
                                    </label>
                                    <input type="text" name="billing_address" placeholder="Enter billing address"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('billing_address') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Financial Information -->
                        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                                <i class="fas fa-wallet" style="margin-right: 0.5rem;"></i> Financial Information
                            </h3>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Balance (৳)
                                    </label>
                                    <input type="number" name="balance" placeholder="Enter balance" step="0.01" min="0"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('balance', 0) }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Due Amount (৳)
                                    </label>
                                    <input type="number" name="due_amount" placeholder="Enter due amount" step="0.01" min="0"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('due_amount', 0) }}">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Credit Limit (৳)
                                    </label>
                                    <input type="number" name="credit_limit" placeholder="Enter credit limit" step="0.01" min="0"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('credit_limit', 0) }}">
                                </div>

                                <div>
                                    <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                        Tax Number
                                    </label>
                                    <input type="text" name="tax_number" placeholder="Enter tax number"
                                        style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s;"
                                        onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';"
                                        value="{{ old('tax_number') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Additional Notes -->
                        <div style="margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                                <i class="fas fa-sticky-note" style="margin-right: 0.5rem;"></i> Additional Information
                            </h3>

                            <div>
                                <label style="display: block; color: #003d5c; font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">
                                    Notes & Remarks
                                </label>
                                <textarea name="notes" rows="4" placeholder="Enter any additional notes or remarks"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #bdbdbd; border-radius: 2px; font-size: 0.9rem; background: white; color: #003d5c; outline: none; transition: all 0.2s; resize: vertical;"
                                    onfocus="this.style.borderColor = '#1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                    onblur="this.style.borderColor = '#bdbdbd'; this.style.boxShadow = 'none';">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #f0f0f0;">
                            <button type="submit" style="flex: 1; background: linear-gradient(135deg, #003d5c 0%, #004d70 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 2px; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; text-transform: uppercase; display: flex; align-items: center; justify-content: center;"
                                    onmouseover="this.style.boxShadow = '0 4px 12px rgba(0, 61, 92, 0.4)'; this.style.transform = 'translateY(-2px)';"
                                    onmouseout="this.style.boxShadow = 'none'; this.style.transform = 'translateY(0)';">
                                <i class="fas fa-save" style="margin-right: 0.5rem;"></i> Create Supplier
                            </button>
                            <a href="{{ route('suppliers.index') }}" style="flex: 1; background: #e0e0e0; color: #003d5c; padding: 1rem 2rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.3s; text-transform: uppercase;"
                               onmouseover="this.style.background = '#d0d0d0';"
                               onmouseout="this.style.background = '#e0e0e0';">
                                <i class="fas fa-times" style="margin-right: 0.5rem;"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Tips Card -->
                <div style="background: white; border: 1px solid #d0d0d0; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden; margin-bottom: 1.5rem;">
                    <div style="background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); padding: 1rem; border-bottom: 3px solid #d4af37;">
                        <h4 style="margin: 0; color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i> Required Fields
                        </h4>
                    </div>
                    <div style="padding: 1.5rem;">
                        <ul style="margin: 0; padding-left: 1.5rem; color: #666; font-size: 0.85rem; line-height: 1.8;">
                            <li style="margin-bottom: 0.5rem;"><strong style="color: #003d5c;">Name</strong> - Supplier name</li>
                            <li style="margin-bottom: 0.5rem;"><strong style="color: #003d5c;">Phone</strong> - Contact number</li>
                            <li>Both fields must be unique</li>
                        </ul>
                    </div>
                </div>

                <!-- Benefits Card -->
                <div style="background: #e8f5e9; border: 1px solid #c8e6c9; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden; margin-bottom: 1.5rem;">
                    <div style="background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); padding: 1rem; border-bottom: 3px solid #d4af37;">
                        <h4 style="margin: 0; color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Benefits
                        </h4>
                    </div>
                    <div style="padding: 1.5rem;">
                        <ul style="margin: 0; padding-left: 1.5rem; color: #1b5e20; font-size: 0.85rem; line-height: 1.8;">
                            <li>Track purchase history</li>
                            <li>Manage supplier dues</li>
                            <li>Quick POS selection</li>
                            <li>Better relationships</li>
                        </ul>
                    </div>
                </div>

                <!-- Important Notice Card -->
                <div style="background: #fff3e0; border: 1px solid #ffe0b2; border-left: 4px solid #f57c00; border-radius: 2px; padding: 1.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; color: #e65100; font-size: 0.95rem; font-weight: 700;">
                        <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> Important
                    </h4>
                    <p style="margin: 0; color: #bf360c; font-size: 0.85rem; line-height: 1.6;">
                        Supplier will be available immediately after creation for use in purchase orders and POS system.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('adminlte_js')
<script>
document.getElementById('supplierForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route("suppliers.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showFlashMessage('✓ Supplier created successfully.', 'success');
            setTimeout(() => window.location.href = '{{ route("suppliers.index") }}', 1500);
        } else {
            showFlashMessage('❌ Error: ' + (data.message || 'Could not create supplier'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFlashMessage('❌ Error creating supplier. Please try again.', 'error');
    });
});

function showFlashMessage(message, type = 'info') {
    const colors = {
        'success': { bg: '#c8e6c9', color: '#1b5e20', border: '#81c784' },
        'error': { bg: '#ffcdd2', color: '#b71c1c', border: '#ef9a9a' },
        'info': { bg: '#bbdefb', color: '#0d47a1', border: '#90caf9' }
    };
    
    const style = colors[type] || colors['info'];
    
    const el = document.createElement('div');
    el.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        background: ${style.bg};
        color: ${style.color};
        border: 2px solid ${style.border};
        border-radius: 2px;
        padding: 1rem 1.25rem;
        font-weight: 600;
        font-size: 0.9rem;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out, slideOut 0.3s ease-in 2.7s forwards;
    `;
    
    el.textContent = message;
    document.body.appendChild(el);
    
    setTimeout(() => el.remove(), 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideOut {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(20px); }
    }
`;
document.head.appendChild(style);
</script>
@endsection