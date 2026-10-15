<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'lembaga',
    ];

    /**
     * Scope hanya role yang diikat ke sebuah lembaga (role monitoring per lembaga).
     */
    public function scopeScoped($query)
    {
        return $query->whereNotNull('lembaga');
    }

    /**
     * Label tampilan: "Monitoring SMK ..." bila role terikat lembaga, selain itu nama role.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->lembaga) {
            return 'Monitoring ' . $this->lembaga;
        }

        return ucfirst($this->name);
    }
}
