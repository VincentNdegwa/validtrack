<?php

namespace App\Models;

use App\Traits\HasSlug;
use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    use HasSlug;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
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
     * Get the company that owns the role.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
