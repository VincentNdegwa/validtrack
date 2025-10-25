<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserToken extends Model
{
    /** @use HasFactory<\Database\Factories\UserTokenFactory> */
    use HasFactory;

    protected $table = 'user_tokens';

    protected $fillable = [
        'user_id',
        'facebook_id',
        'facebook_token',
        'facebook_refresh_token',
        'google_id',
        'google_token',
        'google_refresh_token',
        'twitter_id',
        'twitter_token',
        'twitter_refresh_token',
        'linkedin_id',
        'linkedin_token',
        'linkedin_refresh_token',
        'github_id',
        'github_token',
        'github_refresh_token',
        'instagram_id',
        'instagram_token',
        'instagram_refresh_token',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
