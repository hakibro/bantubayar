<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDOException;

class CreateTestingDatabase extends Command
{
    protected $signature = 'db:create-testing';

    protected $description = 'Membuat database MySQL untuk testing bila belum ada (bantubayar_testing)';

    public function handle(): int
    {
        $config = config('database.connections.mysql');
        $database = 'bantubayar_testing';

        $this->info("Memastikan database testing '{$database}' tersedia...");

        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $database
            ));

            $this->info("Database testing '{$database}' siap.");

            return self::SUCCESS;
        } catch (PDOException $e) {
            $this->error("Gagal membuat database testing: {$e->getMessage()}");
            $this->line('Pastikan service MySQL (lerd-mysql) sedang berjalan dan kredensial di .env benar.');

            return self::FAILURE;
        }
    }
}
