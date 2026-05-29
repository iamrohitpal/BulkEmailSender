<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\SmtpSetting;
use App\Jobs\SendCampaignMailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResumeCampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a campaign with a Google Drive link.
     */
    public function test_create_campaign_with_drive_link(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        // Upload Campaign details with resume link
        $response = $this->actingAs($user, 'web')
            ->post(route('user.campaigns.store'), [
                'name' => 'IT Developer Campaign',
                'subject' => 'Application for Position - View Resume: {{resume_link}}',
                'body' => 'Hello, please check my link: {{resume_link}}',
                'csv_file' => UploadedFile::fake()->createWithContent('contacts.csv', "email,company_name\nhr@google.com,Google\n"),
                'resume_choice' => 'link',
                'resume_link' => 'https://drive.google.com/file/d/test-file-id/view',
            ]);

        $response->assertRedirect(route('user.campaigns.map'));
        
        $draft = session('campaign_draft');
        $this->assertEquals('https://drive.google.com/file/d/test-file-id/view', $draft['resume_link']);
        $this->assertNull($draft['resume_path']);

        // Process Mapping
        $response = $this->actingAs($user, 'web')
            ->post(route('user.campaigns.map.store'), [
                'mapping' => [
                    'email' => '0',
                    'company_name' => '1',
                ]
            ]);

        $response->assertRedirect();
        
        $campaign = Campaign::first();
        $this->assertEquals('https://drive.google.com/file/d/test-file-id/view', $campaign->resume_link);
        $this->assertNull($campaign->resume_path);

        // Check dynamic replacement in Queue Job
        $log = EmailLog::first();
        
        // Mock Mail
        Mail::fake();

        // Seed SMTP settings to prevent job failures
        SmtpSetting::create([
            'user_id' => $user->id,
            'name' => 'Test SMTP',
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'test',
            'password' => 'test',
            'from_address' => 'applicant@gmail.com',
            'from_name' => 'Applicant Name',
            'is_active' => true,
        ]);

        $job = new SendCampaignMailJob($log);
        $job->handle();

        Mail::assertSent(\App\Mail\SendCompanyMail::class, function ($mail) {
            return str_contains($mail->mailSubject, 'https://drive.google.com/file/d/test-file-id/view') &&
                   str_contains($mail->mailBody, 'https://drive.google.com/file/d/test-file-id/view');
        });
    }

    /**
     * Test creating a campaign with a local PDF attachment.
     */
    public function test_create_campaign_with_local_pdf(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['is_active' => true]);

        // Upload Campaign details with local resume file
        $response = $this->actingAs($user, 'web')
            ->post(route('user.campaigns.store'), [
                'name' => 'QA Engineer Campaign',
                'subject' => 'QA Role application',
                'body' => 'See attached resume.',
                'csv_file' => UploadedFile::fake()->createWithContent('contacts.csv', "email,company_name\nhr@google.com,Google\n"),
                'resume_choice' => 'local',
                'resume_file' => UploadedFile::fake()->create('my_resume.pdf', 150, 'application/pdf'),
            ]);

        $response->assertRedirect(route('user.campaigns.map'));
        
        $draft = session('campaign_draft');
        $this->assertNotNull($draft['resume_path']);
        $this->assertNull($draft['resume_link']);
        
        // Assert storage has the file
        Storage::disk('local')->assertExists($draft['resume_path']);

        // Process Mapping
        $response = $this->actingAs($user, 'web')
            ->post(route('user.campaigns.map.store'), [
                'mapping' => [
                    'email' => '0',
                    'company_name' => '1',
                ]
            ]);

        $response->assertRedirect();
        
        $campaign = Campaign::first();
        $this->assertNotNull($campaign->resume_path);
        $this->assertNull($campaign->resume_link);

        // Check attachment resolution in Mail sending job
        $log = EmailLog::first();
        Mail::fake();

        // Seed SMTP Settings
        SmtpSetting::create([
            'user_id' => $user->id,
            'name' => 'Test SMTP',
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'test',
            'password' => 'test',
            'from_address' => 'applicant@gmail.com',
            'from_name' => 'Applicant Name',
            'is_active' => true,
        ]);

        $job = new SendCampaignMailJob($log);
        $job->handle();

        Mail::assertSent(\App\Mail\SendCompanyMail::class, function ($mail) use ($campaign) {
            return $mail->resumePath !== null &&
                   str_contains($mail->resumePath, basename($campaign->resume_path));
        });
    }
}
