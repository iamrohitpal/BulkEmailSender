<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmtpSettingRequest;
use App\Models\SmtpSetting;
use App\Services\MailConfigurationService;
use App\Mail\SendCompanyMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SmtpSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $smtpSettings = SmtpSetting::latest()->get();
        return view('admin.smtp.index', compact('smtpSettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.smtp.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmtpSettingRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        if ($data['is_active']) {
            SmtpSetting::where('is_active', true)->update(['is_active' => false]);
        }

        SmtpSetting::create($data);

        return redirect()->route('admin.smtp.index')
            ->with('success', 'SMTP settings created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmtpSetting $smtp)
    {
        return view('admin.smtp.edit', compact('smtp'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSmtpSettingRequest $request, SmtpSetting $smtp)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        if ($data['is_active']) {
            SmtpSetting::where('is_active', true)
                ->where('id', '!=', $smtp->id)
                ->update(['is_active' => false]);
        }

        $smtp->update($data);

        return redirect()->route('admin.smtp.index')
            ->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmtpSetting $smtp)
    {
        $smtp->delete();

        // If we deleted the active one, mark the latest one as active
        if ($smtp->is_active) {
            $latest = SmtpSetting::latest()->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
            }
        }

        return redirect()->route('admin.smtp.index')
            ->with('success', 'SMTP settings deleted successfully.');
    }

    /**
     * Send a test email using the specified SMTP configurations.
     */
    public function sendTestEmail(Request $request, SmtpSetting $smtp)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = $request->input('test_email');

        try {
            MailConfigurationService::setSmtpConnection($smtp);

            Mail::mailer('dynamic_smtp')
                ->to($testEmail)
                ->send(new SendCompanyMail(
                    'Bulk Mailer - SMTP Test Email',
                    'Congratulations! Your SMTP settings for "' . $smtp->name . '" are correctly configured and working.'
                ));

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $testEmail);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'SMTP Test Failed: ' . $e->getMessage());
        }
    }
}
