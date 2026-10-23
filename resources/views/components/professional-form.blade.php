@props(['title' => '', 'icon' => '', 'backRoute' => '', 'tips' => []])

<div class="row">
    <div class="col-md-8">
        <div class="card" style="border:none; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px;">
            <div class="card-header" style="background:linear-gradient(135deg, #2c5aa0 0%, #1a3a52 100%); border:none; border-radius:8px 8px 0 0; padding:1.4rem;">
                <h5 style="margin:0; font-weight:700; color:#ffffff; font-size:1.1rem;">
                    <i class="{{ $icon }} mr-2"></i>
                    {{ $title }}
                </h5>
            </div>
            <div class="card-body" style="padding:2rem;">
                {{ $slot }}
            </div>
        </div>
    </div>

    @if(!empty($tips))
    <div class="col-md-4">
        <div class="card" style="border:none; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px;">
            <div class="card-header" style="background:linear-gradient(135deg, #2c5aa0 0%, #1a3a52 100%); border:none; border-radius:8px 8px 0 0; padding:1.4rem;">
                <h5 style="margin:0; font-weight:700; color:#ffffff; font-size:1.1rem;">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Tips & Guidelines
                </h5>
            </div>
            <div class="card-body" style="padding:1.4rem;">
                <div style="background:#e8f1f8; border-left:4px solid #2c5aa0; padding:1rem; border-radius:4px;">
                    <h6 style="color:#1a3a52; font-weight:700; margin-bottom:0.8rem;"><i class="fas fa-lightbulb mr-2" style="color:#2c5aa0;"></i> Quick Tips</h6>
                    <ul class="mb-0" style="padding-left:1.5rem;">
                        @foreach($tips as $tip)
                        <li style="font-size:0.9rem; color:#1a3a52; margin-bottom:0.5rem;">{{ $tip }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
