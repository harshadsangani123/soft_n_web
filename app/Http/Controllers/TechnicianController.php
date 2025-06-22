<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complaint;


class TechnicianController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $complaints = Complaint::forTechnician($user->id)->get();
        return view('technician.dashboard');
    }
}
