<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center"
       data-toggle="dropdown" href="#" style="gap:6px;">
        @if(app()->getLocale() == 'bn')
            <img src="https://flagcdn.com/w20/bd.png" width="20" alt="BD">
            <span style="font-size:0.85rem; font-weight:600;">বাংলা</span>
        @elseif(app()->getLocale() == 'ar')
            <img src="https://flagcdn.com/w20/sa.png" width="20" alt="AR">
            <span style="font-size:0.85rem; font-weight:600;">العربية</span>
        @else
            <img src="https://flagcdn.com/w20/gb.png" width="20" alt="EN">
            <span style="font-size:0.85rem; font-weight:600;">English</span>
        @endif
        <i class="fas fa-chevron-down" style="font-size:0.65rem;"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right shadow-sm"
         style="min-width:150px; border:none; border-radius:8px; padding:6px;">
        <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale()=='en' ? 'active' : '' }}"
           href="{{ route('language.switch', 'en') }}" style="gap:10px; border-radius:6px;">
            <img src="https://flagcdn.com/w20/gb.png" width="20" alt="EN">
            <span style="font-size:0.85rem;">English</span>
        </a>
        <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale()=='bn' ? 'active' : '' }}"
           href="{{ route('language.switch', 'bn') }}" style="gap:10px; border-radius:6px;">
            <img src="https://flagcdn.com/w20/bd.png" width="20" alt="BD">
            <span style="font-size:0.85rem;">বাংলা</span>
        </a>
        <a class="dropdown-item d-flex align-items-center py-2 {{ app()->getLocale()=='ar' ? 'active' : '' }}"
           href="{{ route('language.switch', 'ar') }}" style="gap:10px; border-radius:6px;">
            <img src="https://flagcdn.com/w20/sa.png" width="20" alt="AR">
            <span style="font-size:0.85rem;">العربية</span>
        </a>
    </div>
</li>
