<body>
<header>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <a href="<?= $init->baseDir ?>/hako-main.php" class="navbar-brand"><?= $init->title ?></a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="<?= $init->baseDir ?>/hako-main.php?mode=conf">島の登録・設定変更</a></li>
            <li><a href="<?= $init->baseDir ?>/hako-ally.php">同盟管理</a></li>
            <li><a href="<?= $init->baseDir ?>/hako-main.php?mode=log">最近の出来事</a></li>
            <li><a href="https://github.com/Sotalbireo/hakoniwa/wiki" target="_blank">Wiki</a></li>
            <!-- <li><a href="<?= $init->baseDir ?>/manual/" target="_blank">マニュアル</a></li> -->
        </ul>
    </div>
</nav>
</header>
<div class="container">
<?php if (DEBUG) {
    dump($_GET, $_POST);
} ?>
