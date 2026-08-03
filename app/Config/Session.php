<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Session extends BaseConfig
{
    /**
     * ====================================================================
     * ARSITEKTUR DRIVER SESSION AMAN - ANTI BOTTLENECK WINDOWS
     * ====================================================================
     */
    public string $driver = \CodeIgniter\Session\Handlers\DatabaseHandler::class; // Mengubah dari FileHandler ke DatabaseHandler

    public string $cookieName = 'ci_session';

    public int $expiration = 7200;

    // Menunjuk langsung ke nama tabel session yang telah kita buat di MySQL
    public string $savePath = 'ci_sessions';

    public bool $matchIP = false;

    public int $timeToUpdate = 300;

    public bool $regenerateDestroy = false;

    /**
     * ====================================================================
     * KONTROL PROTEKSI COOKIE
     * ====================================================================
     */
    public ?string $DBGroup = 'default'; // Menggunakan koneksi database utama database.php
}
