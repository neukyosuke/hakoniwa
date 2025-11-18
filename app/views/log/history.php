<div id="HistoryLog">
	<h2>更新履歴</h2>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">2025年11月18日</h3>
		</div>
		<div class="panel-body">
			<h4>改善・修正</h4>
			<ul>
				<li><strong>マップ表示の改善</strong>
					<ul>
						<li>マップタイルのサイズを調整し、より見やすくなりました</li>
						<li>島の表示がより鮮明になりました</li>
					</ul>
				</li>
				<li><strong>履歴機能の追加</strong>
					<ul>
						<li>最新5件の履歴を表示する機能を追加しました</li>
					</ul>
				</li>
				<li><strong>同盟機能の修正</strong>
					<ul>
						<li>同盟関連の処理を改善しました</li>
						<li>GM権限周りの動作を修正しました</li>
					</ul>
				</li>
				<li><strong>記念碑の追加</strong>
					<ul>
						<li>新しい記念碑（ドイツのトリ）を追加しました</li>
					</ul>
				</li>
				<li><strong>動作の安定性向上</strong>
					<ul>
						<li>PHP 8.3環境に対応しました</li>
						<li>エラー表示が出にくくなりました</li>
						<li>ターン処理のパフォーマンスを改善しました</li>
						<li>各種バグを修正しました</li>
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
