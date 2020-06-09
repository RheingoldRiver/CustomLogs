<?php

class CustomLogCreator {
	public static function createLog() {
		$logList = self::getCustomLogList();
		global $wgLogTypes, $wgLogActionsHandlers;
		$wgLogTypes = array_merge($wgLogTypes, $logList);
		foreach ($logList as $log) {
			$wgLogActionsHandlers[$log . '/*'] = CustomLogFormatter::class;
		}
	}
	
	public static function getCustomLogList() {
		global $wgCustomLogsLogs;
		return $wgCustomLogsLogs;
	}
}