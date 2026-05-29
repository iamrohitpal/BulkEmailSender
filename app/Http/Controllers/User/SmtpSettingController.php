<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmtpSettingRequest;
use App\Models\SmtpSetting;
use App\Services\MailConfigurationService;
use App\Mail\SendCompanyMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SmtpSettingController extends Controller
{
    /**
     * Display a listing of the user's SMTP profiles.
     */
    public function index()
    {
        $smtpSettings = Auth::guard('web')->user()->smtpSettings()->latest()->get();
        return view('user.smtp.index', compact('smtpSettings'));
    }

    /**
     * Show the form for creating a new SMTP connection.
     */
    public function create()
    {
        return view('user.smtp.create');
    }

    /**
     * Store a newly created SMTP profile.
     */
    public function store(StoreSmtpSettingRequest $request)
    {
        $user = Auth::guard('web')->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['is_active'] = $request->has('is_active');

        if ($data['is_active']) {
            $user->smtpSettings()->where('is_active', true)->update(['is_active' => false]);
        }

        SmtpSetting::create($data);

        return redirect()->route('user.smtp.index')
            ->with('success', 'SMTP server profile configured successfully.');
    }

    /**
     * Show the form for editing the SMTP profile.
     */
    public function edit(SmtpSetting $smtp)
    {
        $this->authorizeOwner($smtp);

        return view('user.smtp.edit', compact('smtp'));
    }

    /**
     * Update the SMTP profile.
     */
    public function update(StoreSmtpSettingRequest $request, SmtpSetting $smtp)
    {
        $this->authorizeOwner($smtp);
        $user = Auth::guard('web')->user();
        
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        if ($data['is_active']) {
            $user->smtpSettings()
                ->where('id', '!=', $smtp->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $smtp->update($data);

        return redirect()->route('user.smtp.index')
            ->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Remove the SMTP profile.
     */
    public function destroy(SmtpSetting $smtp)
    {
        $this->authorizeOwner($smtp);
        
        $user = Auth::guard('web')->user();
        $smtp->delete();

        if ($smtp->is_active) {
            $latest = $user->smtpSettings()->latest()->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
            }
        }

        return redirect()->route('user.smtp.index')
            ->with('success', 'SMTP settings deleted successfully.');
    }

    /**
     * Send a test email.
     */
    public function sendTestEmail(Request $request, SmtpSetting $smtp)
    {
        $this->authorizeOwner($smtp);

        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = $request->input('test_email');

        try {
            MailConfigurationService::setSmtpConnection($smtp);

            Mail::mailer('dynamic_smtp')
                ->to($testEmail)
                ->send(new SendCompanyMail(
                    'SaaS Job Apply Portal - SMTP Test Email',
                    'Success! Your personal SMTP configurations for "' . $smtp->name . '" are working correctly.'
                ));

            return redirect()->back()->with('success', 'Test email sent successfully to ' . $testEmail);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'SMTP connection error: ' . $e->getMessage());
        }
    }

    /**
     * Authorize that the current user owns the setting.
     */
    protected function authorizeOwner(SmtpSetting $smtp)
    {
        if ($smtp->user_id !== Auth::guard('web')->id()) {
            abort(403, 'Unauthorized SMTP profile access.');
        }
    }
}
