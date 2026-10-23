<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            // Purono photo muchhe felun (notun + purono dui location thekei — storage:link charai cholbe)
            if ($user->avatar) {
                $oldPublic = public_path(ltrim($user->avatar, '/'));
                if (is_file($oldPublic)) {
                    @unlink($oldPublic);
                }
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $oldUploadsCopy = public_path('uploads/' . ltrim($user->avatar, '/'));
                if (is_file($oldUploadsCopy)) {
                    @unlink($oldUploadsCopy);
                }
            }

            // storage:link LAGBE NA — sorasori public/uploads/avatars e save (live/shared hosting safe)
            $file = $request->file('avatar');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/avatars');
            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }
}
