#!/usr/bin/env php
<?php
/**
 * 船データ移行スクリプト
 * 旧フォーマット（5ビット島ID）から新フォーマット（10ビット島ID）へ変換
 *
 * 使用方法：
 * 1. バックアップを取る: cp -r neulogs neulogs.backup
 * 2. スクリプト実行: php migrate_ship_data.php
 */

if (!defined('DEBUG')) {
    define('DEBUG', false);
}
if (!defined('READ_LINE')) {
    define('READ_LINE', 8192);
}

require_once __DIR__.'/config.php';
require_once __DIR__.'/hako-init-default.php';

// $initオブジェクトを初期化
$init = new InitDefault();

/**
 * 旧フォーマットで船データをアンパック（5ビット島ID）
 */
function navyUnpackOld($lv) {
    $flag = $lv & 0x0f;
    $lv >>= 4;
    $exp  = $lv & 0x0f;
    $lv >>= 4;
    $hp   = $lv & 0x0f;
    $lv >>= 4;
    $kind = $lv & 0x07;
    $lv >>= 3;
    $id   = $lv & 0x1f;  // 旧: 5 bits

    return [$id, $kind, $hp, $exp, $flag];
}

/**
 * 新フォーマットで船データをパック（10ビット島ID）
 */
function navyPackNew($id, $kind, $hp, $exp, $flag) {
    $id = (int)$id;
    $exp  = min($exp, 15);
    $flag = min($flag, 15);

    $lv   = 0;
    $lv |= $id   & 0x3ff;  // 新: 10 bits
    $lv <<= 3;
    $lv |= $kind & 0x07;
    $lv <<= 4;
    $lv |= $hp   & 0x0f;
    $lv <<= 4;
    $lv |= $exp  & 0x0f;
    $lv <<= 4;
    $lv |= $flag & 0x0f;

    return $lv;
}

/**
 * 島ファイルを変換
 */
function convertIslandFile($islandFile, $islandSize, $landShip) {
    if (!file_exists($islandFile)) {
        return 0;
    }

    $lines = file($islandFile, FILE_IGNORE_NEW_LINES);
    if ($lines === false || count($lines) < $islandSize) {
        echo "警告: {$islandFile} の読み込みに失敗\n";
        return 0;
    }

    $converted = 0;
    $offset = 7; // 一対のデータが何文字か (地形2文字+地形値5文字)

    // 地形データの各行を処理
    for ($y = 0; $y < $islandSize; $y++) {
        $line = $lines[$y];
        $newLine = '';

        for ($x = 0; $x < $islandSize; $x++) {
            $l = mb_substr($line, $x * $offset, 2);      // 地形タイプ
            $v = mb_substr($line, $x * $offset + 2, 5);  // 地形値

            $landType = hexdec($l);
            $landValue = hexdec($v);

            // 船がいる地形のみ処理
            if ($landType == $landShip && $landValue > 0) {
                // 旧フォーマットでアンパック
                [$id, $kind, $hp, $exp, $flag] = navyUnpackOld($landValue);

                // 新フォーマットでパック
                $newValue = navyPackNew($id, $kind, $hp, $exp, $flag);

                $v = sprintf("%05x", $newValue);
                $converted++;
            }

            $newLine .= $l . $v;
        }

        $lines[$y] = $newLine;
    }

    // ファイルに書き戻し
    if ($converted > 0) {
        file_put_contents($islandFile, implode("\n", $lines) . "\n");
    }

    return $converted;
}

// メイン処理
echo "=== 船データ移行スクリプト ===\n";
echo "旧フォーマット（5ビット島ID）→ 新フォーマット（10ビット島ID）\n\n";

$dataFile = $init->dirName.'/hakojima.dat';
$backupDir = $init->dirName.'.backup';

// バックアップ確認
if (!is_dir($backupDir)) {
    echo "警告: バックアップディレクトリが見つかりません。\n";
    echo "続行する前に以下のコマンドでバックアップを作成してください：\n";
    echo "  cp -r {$init->dirName} {$backupDir}\n";
    echo "\n続行しますか？ (y/N): ";
    $input = trim(fgets(STDIN));
    if (strtolower($input) !== 'y') {
        echo "中止しました。\n";
        exit(0);
    }
}

if (!file_exists($dataFile)) {
    echo "エラー: データファイルが見つかりません: {$dataFile}\n";
    exit(1);
}

echo "データファイル読み込み中...\n";

// 島のIDリストを取得
$fp = fopen($dataFile, "r");
$islandTurn = (int)rtrim(fgets($fp, READ_LINE));
$islandLastTime = (int)rtrim(fgets($fp, READ_LINE));
$str = rtrim(fgets($fp, READ_LINE));
[$islandNumber] = explode(",", $str);
$islandNextID = (int)rtrim(fgets($fp, READ_LINE));

echo "ターン: {$islandTurn}\n";
echo "島の数: {$islandNumber}\n";
echo "次の島ID: {$islandNextID}\n\n";

// 各島のIDを収集
$islandIds = [];
for ($i = 0; $i < $islandNumber; $i++) {
    // 1行目: 島名など（スキップ）
    fgets($fp, READ_LINE);
    // 2行目: ID, starturn, isBF, keep
    $line = rtrim(fgets($fp, READ_LINE));
    [$id] = explode(",", $line);
    $islandIds[] = (int)$id;

    // 残りの行をスキップ（島データは21行）
    for ($j = 0; $j < 19; $j++) {
        fgets($fp, READ_LINE);
    }
}

fclose($fp);

echo "島ファイルを変換中...\n";

$totalShips = 0;
foreach ($islandIds as $id) {
    $islandFile = $init->dirName."/island.{$id}";
    $converted = convertIslandFile($islandFile, $init->islandSize, $init->landShip);

    if ($converted > 0) {
        echo "island.{$id}: {$converted} 隻の船データを変換\n";
        $totalShips += $converted;
    }
}

echo "\n変換完了: 合計 {$totalShips} 隻\n";

if ($totalShips > 0) {
    echo "\n成功！船データの移行が完了しました。\n";
} else {
    echo "\n注意: 変換された船データがありません。\n";
    echo "既に新フォーマットか、船が存在しないか確認してください。\n";
}

echo "\n重要: サーバー上のデータも同じ手順で移行してください。\n";
echo "1. バックアップ: cp -r neulogs neulogs.backup\n";
echo "2. このスクリプトをサーバーにアップロード\n";
echo "3. サーバー上で実行: php migrate_ship_data.php\n";
