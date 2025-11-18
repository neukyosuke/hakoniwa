<div class="HistoryLog">
	<!--カスタマイズ-->
	<h2><i class="fas fa-fish"></i> History</h2>
	<ul class="list-unstyled">
	<?php
        $log = new Log;
        $log->historyPrint5();
    ?>
	</ul>
</div>
