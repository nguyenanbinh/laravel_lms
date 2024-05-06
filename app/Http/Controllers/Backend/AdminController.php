<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * dashboard
     *
     * @return View
     */
    public function dashboard(): View
    {
        return view('admin.index');
    }

    /**
     * profile
     *
     * @return View
     */
    public function profile(): View
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);

        return view('admin.admin_profile_view', compact('profileData'));
    }
}
