<?php

class CustomLogCreator {
	public static function createLog() {
		$logList = self::getCustomLogList();
		global $wgLogTypes, $wgLogActionsHandlers;
		$wgLogTypes = array_merge($wgLogTypes, $logList);
		foreach ($wgLogTypes as $log) {
			$wgLogActionsHandlers["custom/$log"] = CustomLogFormatter::class;
		}
	}
	
	public static function getCustomLogList() {
		$logMessageContent = wfMessage('customlogs')->plain();
		return preg_split('/\s*,\s*/',$logMessageContent);
	}
}