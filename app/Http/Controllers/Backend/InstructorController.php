<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function dashboard(): View
    {
        return view('instructor.instructor_dashboard');
    }
}
