<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LembagaKelas extends Model
{
    protected $table = 'v_lembaga_kelas';
    protected $primaryKey = 'idkelas';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    const ASRAMA_IDUNIT = '07';
    const MADIN_IDUNIT  = '01';

    public function scopeFormal($query)
    {
        return $query->whereNotIn('idunit', [self::ASRAMA_IDUNIT, self::MADIN_IDUNIT]);
    }

    public function scopeAsrama($query)
    {
        return $query->where('idunit', self::ASRAMA_IDUNIT);
    }

    public function scopeMadin($query)
    {
        return $query->where('idunit', self::MADIN_IDUNIT);
    }
}
