@if(Session::has('original_user_id') && Session::has('impersonate'))
    {{-- Banner normal de impersonate --}}
    @php
        $originalUser = App\Models\User::find(Session::get('original_user_id'));
        $currentUser = Auth::user();
    @endphp
    
    <div class="impersonate-banner alert alert-warning d-flex justify-content-between align-items-center mb-0" role="alert">
        <div class="impersonate-info">
            <i class="fas fa-user-secret mr-2"></i>
            <strong>Modo Impersonate Ativo:</strong>
            Você está impersonando <strong>{{ $currentUser->name }}</strong> 
            ({{ $currentUser->email }})
            <span class="text-muted">| Usuário original: {{ $originalUser->name }}</span>
        </div>
        
        <form method="POST" action="{{ route('admin.stop-impersonate') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt mr-1"></i>
                Sair do Impersonate
            </button>
        </form>
    </div>
@endif