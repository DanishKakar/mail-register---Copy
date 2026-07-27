<?php
/**
 * Database connection (PDO, prepared statements only — never concatenate
 * user input into SQL).
 * Update the four constants below to match your MySQL server.
 */

const DB_HOST = '127.0.0.1';
// const DB_NAME = 'mailregister';
const DB_NAME = 'mailregister_db';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never leak DB credentials/details to the browser.
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('د ډیټابیس سره وصلیدل ناکام شول. مهرباني وکړئ وروسته بیا هڅه وکړئ.');
        }
    }

    return $pdo;
}
