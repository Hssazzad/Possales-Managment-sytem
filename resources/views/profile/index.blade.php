@extends('adminlte::page')

@section('title', 'Profile | Possales')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0 font-weight-bold" style="color:#343a40; font-size:1.3rem;">
        <i class="fas fa-user-circle mr-2" style="color:#007bff;"></i> My Profile
    </h1>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
        @endif

        <div class="card" style="border:none; border-radius:12px; box-shadow:0 2px 15px rgba(0,0,0,0.08);">
            <div class="card-body p-4">

                {{-- Avatar Section --}}
                <div class="text-center mb-4">
                    <div style="position:relative; display:inline-block;">
                        <img id="avatarPreview"
                             src="{{ $user->avatar ? asset('storage/'.$user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&size=120&background=007bff&color=fff&rounded=true' }}"
                             alt="Profile Photo"
                             style="width:110px; height:110px; border-radius:50%; object-fit:cover; border:3px solid #007bff; box-shadow:0 3px 12px rgba(0,123,255,0.2);">
                        <label for="avatarInput"
                               style="position:absolute; bottom:4px; right:4px; background:#007bff; border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                            <i class="fas fa-camera" style="color:#fff; font-size:0.7rem;"></i>
                        </label>
                    </div>
                    <h5 class="mt-3 mb-0 font-weight-bold" style="color:#343a40;">{{ $user->name }}</h5>
                    <small class="text-muted">{{ $user->email }}</small>
                </div>

                <hr style="border-color:#f0f0f0;">

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="file" id="avatarInput" name="avatar" accept="image/*" class="d-none">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.82rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.5px;">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror"
                                       style="border-radius:8px; border:1px solid #e9ecef; font-size:0.9rem;">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.82rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.5px;">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror"
                                       style="border-radius:8px; border:1px solid #e9ecef; font-size:0.9rem;">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr style="border-color:#f0f0f0;">
                    <p class="font-weight-bold mb-3" style="font-size:0.82rem; color:#6c757d; text-transform:uppercase; letter-spacing:0.5px;">
                        Change Password
                        <small class="text-muted font-weight-normal">(leave blank to keep current)</small>
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.82rem; color:#6c757d;">New Password</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       style="border-radius:8px; border:1px solid #e9ecef; font-size:0.9rem;"
                                       placeholder="Min 8 characters">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:0.82rem; color:#6c757d;">Confirm Password</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control"
                                       style="border-radius:8px; border:1px solid #e9ecef; font-size:0.9rem;"
                                       placeholder="Repeat password">
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-2">
                        <button type="submit" class="btn btn-primary px-5"
                                style="border-radius:8px; font-size:0.9rem; font-weight:600;">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    body, .content-wrapper { background: #f4f6f9 !important; font-family: 'Inter', sans-serif !important; }
    .form-control:focus { border-color: #007bff !important; box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15) !important; }
</style>
@stop

@section('js')
<script>
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@stop
