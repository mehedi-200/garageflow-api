<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'label'])]
class Permission extends Model
{
    /** Feature permissions available in the app. */
    public const FEATURES = [
        'customers' => 'Customers',
        'vehicles' => 'Vehicles',
        'service_jobs' => 'Service Jobs',
        'invoices' => 'Invoices',
        'users' => 'User Management',
        'roles' => 'Role Management',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
