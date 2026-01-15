<?php

namespace App\Models\Traits;

trait Pembayaran
{
    protected function hitungTotalDariKategori(array $kategori = []): int
    {
        return collect($kategori)
            ->flatMap(fn($k) => $k['items'] ?? [])
            ->sum(fn($i) => (int) ($i['remaining_balance'] ?? 0));
    }
}
