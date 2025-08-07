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
        return Session::has('original_user_id') && Session::has('impersonate');
    }

    public function render()
    {
        return view('components.impersonate-banner');
    }
}