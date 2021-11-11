<?php 

try {
    $config = require getcwd() . '/config/config.php';
    
    $conn = getDbConnection(
        $config['database']['host'], 
        $config['database']['port'], 
        $config['database']['dbname'], 
        $config['database']['username'], 
        $config['database']['password']
    );
    
    $sql = file_get_contents(getcwd() . '/config/database-structure.sql');
    $conn->exec($sql);
    
    echo 'SUCCESS'.PHP_EOL;
} catch (Exception $e) {
    echo 'FAIL TO RUN DB_SETUP.'.PHP_EOL.$e->getMessage().PHP_EOL;
}


function getDbConnection(
    string $host,
    string $port,
    string $dbName,
    string $username,
    string $password
): PDO
{
    try {
        $conn = new PDO("mysql:host={$host};port={$port};", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $conn;
    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
}