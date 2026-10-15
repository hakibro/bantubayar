<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lembaga',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'petugas_siswa', 'petugas_id', 'siswa_id')
            ->withTimestamps();
    }

    public function penanganan()
    {
        return $this->hasMany(Penanganan::class, 'id_petugas');
    }

    public function homeVisits()
    {
        return $this->hasMany(HomeVisit::class, 'admin_id');
    }

    /**
     * Ambil nilai lembaga dari role user yang di-scope ke lembaga (role monitoring per lembaga).
     * Mengembalikan null bila user tidak punya role ber-lembaga (mis. admin/monitoring global).
     */
    public function lembagaScope(): ?string
    {
        $role = $this->roles->firstWhere(fn($role) => !empty($role->lembaga));

        return $role?->lembaga;
    }

    /**
     * True bila user adalah monitoring yang dibatasi ke satu lembaga tertentu.
     * Admin dan role monitoring global dikecualikan agar tetap melihat semua data.
     */
    public function isLembagaMonitor(): bool
    {
        if ($this->hasRole('admin')) {
            return false;
        }

        return $this->lembagaScope() !== null;
    }

}
