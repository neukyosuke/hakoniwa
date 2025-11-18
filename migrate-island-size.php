<?php
/**
 * Island Size Migration Script
 * Migrates island data from 20x20 to 24x24 format
 *
 * Usage: php migrate-island-size.php
 *
 * IMPORTANT: Backup your data directory before running this script!
 */

declare(strict_types=1);

// Configuration
$oldSize = 20;
$newSize = 24;
$dataDir = __DIR__ . '/logs/data';
$backupDir = __DIR__ . '/logs/data_backup_' . date('Y-m-d_H-i-s');

// Read constants
define('READ_LINE', 1024);

echo "Island Size Migration Script\n";
echo "============================\n";
echo "Old size: {$oldSize}x{$oldSize}\n";
echo "New size: {$newSize}x{$newSize}\n";
echo "Data directory: {$dataDir}\n\n";

// Step 1: Create backup
echo "Step 1: Creating backup...\n";
if (!is_dir($dataDir)) {
    die("Error: Data directory not found: {$dataDir}\n");
}

if (!mkdir($backupDir, 0755, true)) {
    die("Error: Failed to create backup directory: {$backupDir}\n");
}

// Copy all files to backup
$files = scandir($dataDir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }
    $srcPath = $dataDir . '/' . $file;
    $dstPath = $backupDir . '/' . $file;
    if (is_file($srcPath)) {
        if (!copy($srcPath, $dstPath)) {
            die("Error: Failed to backup file: {$file}\n");
        }
    }
}
echo "Backup created at: {$backupDir}\n\n";

// Step 2: Find all island files
echo "Step 2: Finding island files...\n";
$islandFiles = [];
foreach ($files as $file) {
    if (preg_match('/^island\.(\d+)$/', $file, $matches)) {
        $islandFiles[] = [
            'filename' => $file,
            'id' => $matches[1],
            'path' => $dataDir . '/' . $file
        ];
    }
}
echo "Found " . count($islandFiles) . " island file(s)\n\n";

if (empty($islandFiles)) {
    echo "No island files to migrate. Exiting.\n";
    exit(0);
}

// Step 3: Migrate each island file
echo "Step 3: Migrating island files...\n";
$offset = 2; // Offset to center the old map in the new map (24-20)/2 = 2

foreach ($islandFiles as $islandFile) {
    echo "Migrating island {$islandFile['id']}... ";

    // Read old format
    $fp = fopen($islandFile['path'], 'r');
    if (!$fp) {
        echo "ERROR: Cannot open file\n";
        continue;
    }

    $oldLand = [];
    $oldLandValue = [];

    // Read 20x20 data
    for ($y = 0; $y < $oldSize; $y++) {
        $line = rtrim(fgets($fp, READ_LINE));
        for ($x = 0; $x < $oldSize; $x++) {
            $l = mb_substr($line, $x * 7, 2);
            $v = mb_substr($line, $x * 7 + 2, 5);
            $oldLand[$x][$y] = $l;
            $oldLandValue[$x][$y] = $v;
        }
    }

    // Read commands (keep as-is)
    $commands = [];
    while (!feof($fp)) {
        $line = fgets($fp, READ_LINE);
        if ($line !== false) {
            $commands[] = $line;
        }
    }
    fclose($fp);

    // Create new 24x24 format with old data centered
    $newLand = [];
    $newLandValue = [];

    // Initialize with sea (terrain code 00)
    for ($y = 0; $y < $newSize; $y++) {
        for ($x = 0; $x < $newSize; $x++) {
            $newLand[$x][$y] = '00';
            $newLandValue[$x][$y] = '00000';
        }
    }

    // Copy old data to center of new map
    for ($y = 0; $y < $oldSize; $y++) {
        for ($x = 0; $x < $oldSize; $x++) {
            $newX = $x + $offset;
            $newY = $y + $offset;
            $newLand[$newX][$newY] = $oldLand[$x][$y];
            $newLandValue[$newX][$newY] = $oldLandValue[$x][$y];
        }
    }

    // Write new format
    $fp = fopen($islandFile['path'], 'w');
    if (!$fp) {
        echo "ERROR: Cannot write file\n";
        continue;
    }

    // Write 24x24 terrain data
    for ($y = 0; $y < $newSize; $y++) {
        for ($x = 0; $x < $newSize; $x++) {
            fwrite($fp, $newLand[$x][$y] . $newLandValue[$x][$y]);
        }
        fwrite($fp, "\n");
    }

    // Write commands (unchanged)
    foreach ($commands as $command) {
        fwrite($fp, $command);
    }

    fclose($fp);
    echo "OK\n";
}

echo "\nMigration completed successfully!\n";
echo "Backup location: {$backupDir}\n";
echo "\nIMPORTANT: Test your game thoroughly before deleting the backup.\n";
echo "If you encounter any issues, restore from backup by copying files back to {$dataDir}\n";
