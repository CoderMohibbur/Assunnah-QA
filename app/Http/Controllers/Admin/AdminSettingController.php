<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        // UI form fixed keys
        $data = [
            'site_title' => Setting::get('site_title', 'As Sunnah Question & Answer'),
            'footer_text' => Setting::get('footer_text', '© '.date('Y').' As Sunnah Q&A'),
            'notify_sms' => Setting::get('notify_sms', '1'),
            'notify_email' => Setting::get('notify_email', '1'),
            'sms_provider' => Setting::get('sms_provider', ''),
        ];

        return view('admin.settings.index', compact('data'));
    }

    public function update(Request $request)
    {
        $v = $request->validate([
            'site_title' => ['required','string','max:190'],
            'footer_text' => ['nullable','string','max:5000'],
            'notify_sms' => ['nullable','in:0,1'],
            'notify_email' => ['nullable','in:0,1'],
            'sms_provider' => ['nullable','string','max:50'],
        ]);

        Setting::put('site_title', $v['site_title'], 'ui');
        Setting::put('footer_text', $v['footer_text'] ?? '', 'ui');
        Setting::put('notify_sms', $v['notify_sms'] ?? '0', 'notify');
        Setting::put('notify_email', $v['notify_email'] ?? '0', 'notify');
        Setting::put('sms_provider', $v['sms_provider'] ?? '', 'sms');

        return back()->with('success', 'Settings saved ✅');
    }
}
