<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ImpersonateBanner extends Component
{
    public function shouldRender()
    {
        // Debug mais detalhado
        $debugInfo = [
            'current_route' => Request::route()->getName(),
            'current_url' => Request::url(),
            'has_original_user_id' => Session::has('original_user_id'),
            'has_impersonate' => Session::has('impersonate'),
            'original_user_id' => Session::get('original_user_id'),
            'impersonate' => Session::get('impersonate'),
            'session_id' => Session::getId(),
            'all_sessions' => Session::all()
        ];
        
        Log::info('ImpersonateBanner Debug Detalhado:', $debugInfo);
        
        return true; // Sempre renderizar para debug
    }

    public function render()
    {
        return view('components.impersonate-banner');
    }
}