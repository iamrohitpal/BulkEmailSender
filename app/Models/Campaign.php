<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'smtp_setting_id',
    'name',
    'subject',
    'body',
    'status',
    'delay_seconds',
    'total_emails',
    'sent_emails',
    'failed_emails',
    'resume_path',
    'resume_link',
])]
class Campaign extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'smtp_setting_id' => 'integer',
            'delay_seconds' => 'integer',
            'total_emails' => 'integer',
            'sent_emails' => 'integer',
            'failed_emails' => 'integer',
        ];
    }

    /**
     * Get the user that owns this campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the SMTP settings associated with this campaign.
     */
    public function smtpSetting(): BelongsTo
    {
        return $this->belongsTo(SmtpSetting::class);
    }

    /**
     * Get the email logs (recipients) for this campaign.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }
}
