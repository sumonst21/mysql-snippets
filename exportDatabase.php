<?php
/**
 * Export the database to an SQL file
 *
 * @param string $host
 * @param string $username
 * @param string $password
 * @param string $database
 * @param string $outputFile
 * @param boolean $replace_charset (optional) - if true, replace the charset to utf8mb4 & collation to utf8mb4_general_ci (based on the current collation) TODO: regex to replace the charset & collation in the exported file
 * @return void
 * @example exportDatabase(HOST, USER, PASSWORD, DB, __DIR__ . '/db.sql', true)
 * @author sumonst21 <sumonst21@gmail.com>
 */
function exportDatabase($host, $username, $password, $database, $outputFile, $replace_charset = false)
{
    try {
        // Connect to the database
        $dsn = "mysql:host=$host;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($replace_charset) {
            // Get the current charset & collation
            $sql = "SELECT default_character_set_name, default_collation_name FROM information_schema.SCHEMATA WHERE schema_name = '$database'";
            $result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            $charset = $result['default_character_set_name'];
            $collation = $result['default_collation_name'];

            // Replace the charset & collation to utf8mb4
            $sql = "ALTER DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            $pdo->exec($sql);

            // Replace the charset & collation to utf8mb4 for each table
            $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = '$database'";
            $result = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            foreach ($result as $table) {
                $sql = "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
                $pdo->exec($sql);
            }
        }

        // Start a transaction
        $pdo->beginTransaction();

        // Get all table names in the database
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        // Open the output file
        $file = fopen($outputFile, 'w');

        // Iterate over each table
        foreach ($tables as $table) {
            // Retrieve the table structure
            $tableStructure = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);

            // Write the "CREATE TABLE IF NOT EXISTS" statement to the output file
            fwrite($file, "-- Table structure for table `$table` --" . PHP_EOL);
            fwrite($file, $tableStructure['Create Table'] . ";" . PHP_EOL);

            // Retrieve the table data
            $tableData = $pdo->query("SELECT * FROM $table");

            // Write the table data to the output file
            fwrite($file, "-- Data for table `$table` --" . PHP_EOL);
            while ($row = $tableData->fetch(PDO::FETCH_ASSOC)) {
                $values = implode("', '", array_map('addslashes', $row));
                fwrite($file, "INSERT INTO `$table` VALUES ('$values');" . PHP_EOL);
            }

            fwrite($file, PHP_EOL);
        }

        // Commit the transaction
        $pdo->commit();

        // Close the output file
        fclose($file);

        echo "Database exported successfully to $outputFile";
    } catch (PDOException $e) {
        // Rollback the transaction if an error occurs
        $pdo->rollBack();

        echo "Error exporting database: " . $e->getMessage();
    }

    $pdo = null;
}

if (!is_writable(__DIR__)) {
    echo 'Directory is not writable, please check the permission';
    exit;
}

// Example usage
$export_file = DB . '_' . date('Y-m-d_H-i-s') . '.sql';
exportDatabase(HOST, USER, PASSWORD, DB, __DIR__ . '/' . $export_file);
