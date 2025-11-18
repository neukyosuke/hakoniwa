<div id="HistoryLog">
	<h2>更新履歴</h2>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">2025年11月</h3>
		</div>
		<div class="panel-body">
			<ul class="list-unstyled">
				<li><strong>2025-11-18</strong>
					<ul>
						<li>PHP 8.3対応のための修正を実施
							<ul>
								<li>未定義配列キーに関する警告を修正（oilincome, money, ship等）</li>
								<li>未定義変数に関する警告を修正（$ZorasuMove, $TrainMove）</li>
								<li>null配列アクセスに関する警告を修正（util.php, hako-turn.php）</li>
								<li>型キャストとstrict比較の追加</li>
								<li>未定義定数エラーの修正</li>
								<li>括弧なしネスト三項演算子の修正</li>
							</ul>
						</li>
						<li>マップタイルサイズを32pxから40pxに変更
							<ul>
								<li>画像表示サイズを正しく設定</li>
								<li>ポップアップウィンドウサイズの調整</li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>

	<h2>海域の近況</h2>
	<ul class="list-unstyled">
	<?php
        $log = new Log;
        $log->historyPrint();
    ?>
	</ul>
</div>
