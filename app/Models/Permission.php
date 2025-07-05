<?php

namespace App\Models;

use App\Traits\HasSlug;
use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    use HasSlug;


    protected $appends = ['slug'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'company_id',
    ];

    /**
     * Get the company that owns the permission.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
