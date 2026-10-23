@extends('adminlte::page')

@section('title', 'Edit Category | PosSales')

@section('content_header')
<!-- Government Style Header -->
<div style="background: linear-gradient(to right, #003d5c 0%, #00516d 100%); border-bottom: 4px solid #d4af37; padding: 0;">
    <div style="max-width: 100%; padding: 1.5rem 2rem;">
        <!-- Official Banner -->
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="margin: 0; color: #d4af37; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">OFFICIAL REGISTRY SYSTEM</p>
                    <h1 style="margin: 0.5rem 0 0 0; color: white; font-size: 2rem; font-weight: 700; font-family: Georgia, serif;">
                        <i class="fas fa-edit" style="margin-right: 1rem; color: #d4af37;"></i>
                        Category Modification Form
                    </h1>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; color: #d4af37; font-size: 0.8rem; font-weight: 600;">Document ID: CAT-MOD-{{ str_pad($category->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p style="margin: 0.25rem 0 0 0; color: #a8a8a8; font-size: 0.75rem;">Last Modified: {{ $category->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <div style="padding-top: 1rem; border-top: 1px solid #00516d;">
            <nav style="display: flex; gap: 1rem; align-items: center;">
                <a href="{{ route('dashboard') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-home"></i> Home
                </a>
                <span style="color: #666;">
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
                <a href="{{ route('categories.index') }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-list"></i> Categories
                </a>
                <span style="color: #666;">
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
                <a href="{{ route('categories.show', $category->id) }}" style="color: #d4af37; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                    <i class="fas fa-folder"></i> {{ $category->name }}
                </a>
                <span style="color: #666;">
                    <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
                <span style="color: #e5e5e5; font-size: 0.85rem;">Modification</span>
            </nav>
        </div>
    </div>
</div>
@stop

@section('content')
<div style="background: #f5f5f5; padding: 2rem 0; min-height: calc(100vh - 200px);">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <!-- Alert Section -->
        <div style="background: #e3f2fd; border-left: 5px solid #1976d2; padding: 1.25rem 1.5rem; border-radius: 2px; margin-bottom: 2rem;">
            <p style="margin: 0; color: #003d5c; font-size: 0.9rem; font-weight: 600;">
                <i class="fas fa-info-circle" style="margin-right: 0.75rem; color: #1976d2;"></i>
                You are editing <strong>{{ $category->name }}</strong>. All changes will be applied to associated products.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem;">
            <!-- Main Form Section -->
            <div style="background: white; border: 1px solid #d0d0d0; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                <!-- Form Header -->
                <div style="background: linear-gradient(to right, #003d5c 0%, #004d70 100%); padding: 1.5rem; border-bottom: 3px solid #d4af37;">
                    <h2 style="margin: 0; color: white; font-size: 1.3rem; font-weight: 700; display: flex; align-items: center;">
                        <i class="fas fa-pencil-alt" style="margin-right: 0.75rem;"></i>
                        Category Details (Edit Mode)
                    </h2>
                    <p style="margin: 0.5rem 0 0 0; color: #b0b0b0; font-size: 0.85rem;">Modify category information below and click "Update Category" to save changes</p>
                </div>

                <!-- Form Body -->
                <form method="POST" action="{{ route('categories.update', $category->id) }}" style="padding: 2rem;">
                    @csrf
                    @method('PUT')

                    <!-- Section 1: Basic Information -->
                    <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                        <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                            <i class="fas fa-info" style="margin-right: 0.5rem;"></i> Basic Information
                        </h3>

                        <!-- Category Name -->
                        <div style="margin-bottom: 1.75rem;">
                            <label for="name" style="display: block; color: #003d5c; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem;">
                                Category Name <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Enter official category name"
                                   required
                                   value="{{ old('name', $category->name) }}"
                                   style="width: 100%; padding: 0.85rem 1rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-family: Arial, sans-serif; transition: all 0.2s; box-sizing: border-box; @error('name') border: 2px solid #d32f2f; background: #ffebee; @enderror"
                                   onfocus="this.style.border = '2px solid #1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                   onblur="this.style.border = '1px solid #bdbdbd'; this.style.boxShadow = 'none';">
                            @error('name')
                                <p style="margin: 0.5rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                    <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                                </p>
                            @enderror
                            <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.8rem; font-style: italic;">
                                <i class="fas fa-asterisk" style="font-size: 0.6rem;"></i> Must be unique and descriptive
                            </p>
                        </div>

                        <!-- Slug -->
                        <div style="margin-bottom: 0;">
                            <label for="slug" style="display: block; color: #003d5c; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem;">
                                URL Slug (Optional)
                            </label>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   placeholder="auto-generated-from-name"
                                   value="{{ old('slug', $category->slug) }}"
                                   style="width: 100%; padding: 0.85rem 1rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-family: monospace; transition: all 0.2s; box-sizing: border-box; background: #fafafa; @error('slug') border: 2px solid #d32f2f; background: #ffebee; @enderror"
                                   onfocus="this.style.border = '2px solid #1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                   onblur="this.style.border = '1px solid #bdbdbd'; this.style.boxShadow = 'none';">
                            @error('slug')
                                <p style="margin: 0.5rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                    <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                                </p>
                            @enderror
                            <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.8rem; font-style: italic;">
                                Leave blank for automatic generation
                            </p>
                        </div>
                    </div>

                    <!-- Section 2: Configuration -->
                    <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                        <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                            <i class="fas fa-cog" style="margin-right: 0.5rem;"></i> Configuration
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <!-- Icon Selection -->
                            <div>
                                <label for="icon" style="display: block; color: #003d5c; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem;">
                                    Category Icon
                                </label>
                                <input type="text" 
                                       id="icon" 
                                       name="icon" 
                                       placeholder="e.g., fa-box"
                                       value="{{ old('icon', $category->icon) }}"
                                       style="width: 100%; padding: 0.85rem 1rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; transition: all 0.2s; box-sizing: border-box; @error('icon') border: 2px solid #d32f2f; background: #ffebee; @enderror"
                                       onfocus="this.style.border = '2px solid #1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                       onblur="this.style.border = '1px solid #bdbdbd'; this.style.boxShadow = 'none';">
                                @error('icon')
                                    <p style="margin: 0.5rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                        <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                                    </p>
                                @enderror
                                <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.8rem;">
                                    FontAwesome icon class
                                </p>
                            </div>

                            <!-- Brand Selection -->
                            <div>
                                <label for="brand_id" style="display: block; color: #003d5c; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem;">
                                    Associated Brand
                                </label>
                                <select id="brand_id" 
                                        name="brand_id"
                                        style="width: 100%; padding: 0.85rem 1rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-family: Arial, sans-serif; transition: all 0.2s; box-sizing: border-box; background: white; cursor: pointer; @error('brand_id') border: 2px solid #d32f2f; background: #ffebee; @enderror"
                                        onfocus="this.style.border = '2px solid #1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                        onblur="this.style.border = '1px solid #bdbdbd'; this.style.boxShadow = 'none';">
                                    <option value="">-- Select Brand --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $category->brand_id) == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <p style="margin: 0.5rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                        <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Product Variations -->
                    <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                        <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                            <i class="fas fa-cubes" style="margin-right: 0.5rem;"></i> Product Variations
                        </h3>

                        <div style="background: #fafafa; padding: 1.5rem; border-radius: 2px; border: 1px solid #e0e0e0;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                                @foreach($variationTypes as $variation)
                                <label style="display: flex; align-items: center; padding: 0.75rem 1rem; background: white; border: 1px solid #d0d0d0; border-radius: 2px; cursor: pointer; transition: all 0.2s; font-weight: 600; color: #003d5c;">
                                    <input type="checkbox" 
                                           name="variations[]" 
                                           value="{{ $variation }}"
                                           {{ (old('variations', $category->variations ?? []) && in_array($variation, old('variations', $category->variations ?? []))) ? 'checked' : '' }}
                                           style="margin-right: 0.75rem; width: 18px; height: 18px; cursor: pointer; accent-color: #1976d2;">
                                    <span style="flex: 1;">{{ $variation }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('variations')
                                <p style="margin: 1rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                    <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                        <p style="margin: 1rem 0 0 0; color: #666; font-size: 0.8rem; font-style: italic;">
                            <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                            Select applicable variations for products in this category
                        </p>
                    </div>

                    <!-- Section 4: Description -->
                    <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 2px solid #f0f0f0;">
                        <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                            <i class="fas fa-pen-fancy" style="margin-right: 0.5rem;"></i> Description
                        </h3>

                        <textarea id="description" 
                                  name="description" 
                                  rows="5"
                                  placeholder="Provide a comprehensive description of this category (optional)"
                                  style="width: 100%; padding: 1rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-family: Arial, sans-serif; resize: vertical; box-sizing: border-box; transition: all 0.2s; @error('description') border: 2px solid #d32f2f; background: #ffebee; @enderror"
                                  onfocus="this.style.border = '2px solid #1976d2'; this.style.boxShadow = '0 0 0 3px #e3f2fd';"
                                  onblur="this.style.border = '1px solid #bdbdbd'; this.style.boxShadow = 'none';">{{ old('description', $category->description) }}</textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;">
                            <p style="margin: 0; color: #666; font-size: 0.8rem;">Maximum 500 characters</p>
                            <span style="color: #999; font-size: 0.85rem; font-weight: 600;" id="charCount">0/500</span>
                        </div>

                        @error('description')
                            <p style="margin: 0.5rem 0 0 0; color: #d32f2f; font-size: 0.85rem;">
                                <i class="fas fa-times-circle" style="margin-right: 0.5rem;"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Section 5: Status -->
                    <div style="margin-bottom: 0; padding-bottom: 0;">
                        <h3 style="margin: 0 0 1.5rem 0; color: #003d5c; font-size: 1.1rem; font-weight: 700; padding-bottom: 0.75rem; border-bottom: 2px solid #d4af37;">
                            <i class="fas fa-toggle-on" style="margin-right: 0.5rem;"></i> Status & Publish
                        </h3>

                        <div style="background: #f5f5f5; padding: 1.5rem; border-radius: 2px; border: 1px solid #d0d0d0;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                       onchange="updateStatusBadge()"
                                       style="width: 20px; height: 20px; cursor: pointer; accent-color: #1976d2; margin-right: 1rem;">
                                <span style="color: #003d5c; font-weight: 700; font-size: 0.95rem;">Active Category</span>
                                <span id="statusBadge" style="margin-left: 1rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">
                                    ACTIVE
                                </span>
                            </label>
                            <p style="margin: 0.75rem 0 0 2.5rem; color: #666; font-size: 0.8rem;">
                                Inactive categories will not be visible to users
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 1rem; margin-top: 2.5rem; padding-top: 2rem; border-top: 2px solid #f0f0f0;">
                        <button type="submit" 
                                style="flex: 1; background: linear-gradient(135deg, #003d5c 0%, #004d70 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 2px; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-transform: uppercase;"
                                onmouseover="this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)'; this.style.transform = 'translateY(-1px)';"
                                onmouseout="this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)'; this.style.transform = 'translateY(0)';">
                            <i class="fas fa-save" style="margin-right: 0.5rem;"></i> Update Category
                        </button>
                        <a href="{{ route('categories.index') }}" 
                           style="flex: 1; background: #e0e0e0; color: #003d5c; padding: 1rem 2rem; border: 1px solid #bdbdbd; border-radius: 2px; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; transition: all 0.3s; text-decoration: none; text-transform: uppercase; display: flex; align-items: center; justify-content: center; text-align: center;"
                           onmouseover="this.style.background = '#d0d0d0'; this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';"
                           onmouseout="this.style.background = '#e0e0e0'; this.style.boxShadow = 'none';">
                            <i class="fas fa-times" style="margin-right: 0.5rem;"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Sidebar: Important Information -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Category Info Card -->
                <div style="background: white; border: 1px solid #d0d0d0; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                    <div style="background: linear-gradient(to right, #1976d2 0%, #1565c0 100%); padding: 1rem 1.5rem; border-bottom: 3px solid #d4af37;">
                        <h4 style="margin: 0; color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-folder-open" style="margin-right: 0.75rem;"></i> Category Info
                        </h4>
                    </div>
                    <div style="padding: 1.5rem;">
                        <div style="margin-bottom: 1rem;">
                            <p style="margin: 0; color: #999; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Category ID</p>
                            <p style="margin: 0.25rem 0 0 0; color: #003d5c; font-size: 0.95rem; font-weight: 700;">{{ $category->id }}</p>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <p style="margin: 0; color: #999; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Created Date</p>
                            <p style="margin: 0.25rem 0 0 0; color: #003d5c; font-size: 0.9rem;">{{ $category->created_at->format('M d, Y - H:i') }}</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #999; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Last Updated</p>
                            <p style="margin: 0.25rem 0 0 0; color: #003d5c; font-size: 0.9rem;">{{ $category->updated_at->format('M d, Y - H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div style="background: white; border: 1px solid #d0d0d0; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                    <div style="background: linear-gradient(to right, #003d5c 0%, #004d70 100%); padding: 1rem 1.5rem; border-bottom: 3px solid #d4af37;">
                        <h4 style="margin: 0; color: white; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-book" style="margin-right: 0.75rem;"></i> Edit Instructions
                        </h4>
                    </div>
                    <div style="padding: 1.5rem;">
                        <ol style="margin: 0; padding-left: 1.5rem; color: #003d5c; font-size: 0.85rem; line-height: 1.6;">
                            <li style="margin-bottom: 0.75rem;">Modify category details as needed</li>
                            <li style="margin-bottom: 0.75rem;">Update icon or brand association</li>
                            <li style="margin-bottom: 0.75rem;">Adjust product variations</li>
                            <li style="margin-bottom: 0.75rem;">Edit description if required</li>
                            <li style="margin-bottom: 0.75rem;">Toggle active status if needed</li>
                            <li>Click "Update Category" to save</li>
                        </ol>
                    </div>
                </div>

                <!-- Important Notes -->
                <div style="background: #fff3e0; border: 1px solid #ffe0b2; border-left: 4px solid #f57c00; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                    <div style="padding: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #e65100; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Important
                        </h4>
                        <ul style="margin: 0; padding-left: 1.5rem; color: #bf360c; font-size: 0.85rem; line-height: 1.6;">
                            <li style="margin-bottom: 0.5rem;">Changes apply immediately</li>
                            <li style="margin-bottom: 0.5rem;">Affects all products in category</li>
                            <li style="margin-bottom: 0.5rem;">Category name must be unique</li>
                            <li>All changes are logged</li>
                        </ul>
                    </div>
                </div>

                <!-- System Information -->
                <div style="background: #e3f2fd; border: 1px solid #bbdefb; border-left: 4px solid #1976d2; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
                    <div style="padding: 1.5rem;">
                        <h4 style="margin: 0 0 1rem 0; color: #0d47a1; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center;">
                            <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i> System Info
                        </h4>
                        <div style="color: #1565c0; font-size: 0.85rem; line-height: 1.8;">
                            <p style="margin: 0;">
                                <strong>Form Type:</strong> Category Modification
                            </p>
                            <p style="margin: 0.5rem 0 0 0;">
                                <strong>Version:</strong> 1.0
                            </p>
                            <p style="margin: 0.5rem 0 0 0;">
                                <strong>Last Updated:</strong> April 2026
                            </p>
                            <p style="margin: 0.5rem 0 0 0;">
                                <strong>Support:</strong> <a href="mailto:support@possales.gov" style="color: #1565c0; text-decoration: none; font-weight: 600;">support@possales.gov</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character Counter
        const descriptionInput = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        if (descriptionInput && charCount) {
            descriptionInput.addEventListener('input', function() {
                let count = this.value.length;
                charCount.textContent = count + '/500';
                
                if (count > 500) {
                    this.value = this.value.substring(0, 500);
                    charCount.textContent = '500/500';
                }
            });
            charCount.textContent = descriptionInput.value.length + '/500';
        }

        // Auto-generate Slug
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                if (!slugInput.value || slugInput.value === '') {
                    slugInput.value = this.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            });
        }

        // Initialize Status Badge
        updateStatusBadge();
    });

    function updateStatusBadge() {
        const statusToggle = document.getElementById('is_active');
        const statusBadge = document.getElementById('statusBadge');
        
        if (statusToggle.checked) {
            statusBadge.textContent = 'ACTIVE';
            statusBadge.style.background = '#c8e6c9';
            statusBadge.style.color = '#1b5e20';
        } else {
            statusBadge.textContent = 'INACTIVE';
            statusBadge.style.background = '#ffcdd2';
            statusBadge.style.color = '#b71c1c';
        }
    }
</script>
@stop