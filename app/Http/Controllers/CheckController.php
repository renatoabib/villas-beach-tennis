<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CheckController extends Controller
{
    public function show($memberNumber)
    {
        $user = User::where('member_number', $memberNumber)->firstOrFail();
        $financial = $user->getMembershipFinancialStatus();

        return view('check.status', compact('user', 'financial'));
    }

    public function scanner()
    {
        return view('admin.qr-scanner');
    }

    public function qrCodeImage($memberNumber)
    {
        $path = "qrcodes/{$memberNumber}.svg";

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $contents = Storage::disk('public')->get($path);

        return response($contents, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
