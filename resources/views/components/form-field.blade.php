@props(['label' => '', 'name' => '', 'type' => 'text', 'placeholder' => '', 'value' => '', 'required' => false, 'error' => null, 'helper' => ''])

<div class="form-group" style="margin-bottom:1.8rem;">
    @if($label)
    <label for="{{ $name }}" class="form-label" style="color:#1a3a52; font-weight:700; font-size:1rem; margin-bottom:0.7rem; display:block;">
        {{ $label }}{{ $required ? ' *' : '' }}
    </label>
    @endif

    @if($type === 'textarea')
        <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" style="border:1px solid #d0dce8; padding:0.8rem 1rem; font-size:0.95rem; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:5px;">{{ $value }}</textarea>
    @elseif($type === 'select')
        <select class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" style="border:1px solid #d0dce8; padding:0.8rem 1rem; font-size:0.95rem; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:5px;">
            {{ $slot }}
        </select>
    @else
        <input type="{{ $type }}" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $placeholder }}" value="{{ $value }}" {{ $required ? 'required' : '' }} style="border:1px solid #d0dce8; padding:0.8rem 1rem; font-size:0.95rem; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:5px;">
    @endif

    @if($helper)
    <small class="text-muted" style="font-size:0.8rem; color:#666; display:block; margin-top:0.5rem;">{{ $helper }}</small>
    @endif

    @error($name)
    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
    @enderror
</div>
