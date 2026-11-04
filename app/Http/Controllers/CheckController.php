<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function show($memberNumber)
    {
        $user = User::where('member_number', $memberNumber)->firstOrFail();
        $financial = $user->getMembershipFinancialStatus();

        return view('check.status', compact('user', 'financial'));
    }
}
