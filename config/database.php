<?php
/**
 * ClassicCart - Database Configuration
 * Edit these values if your local MySQL setup is different.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'classiccart');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Returns a shared PDO connection instance.
 *
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                )
            );
        } catch (PDOException $e) {
            die('Database connection could not be established. Please check your configuration in config/database.php and make sure the database was imported.');
        }
    }

    return $pdo;
}