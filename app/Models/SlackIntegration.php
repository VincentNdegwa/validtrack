<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlackIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'app_id',
        'authed_user_id',
        'scope',
        'token_type',
        'access_token',
        'bot_user_id',
        'team_id',
        'team_name',
        'is_enterprise_install',
        'webhook_channel',
        'webhook_channel_id',
        'webhook_configuration_url',
        'webhook_url',
    ];

    protected $casts = [
        'is_enterprise_install' => 'boolean',
    ];

    // protected $hidden = [
    //     'access_token',
    //     'webhook_url',
    // ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
