<?php

class ApiCustomLogCreator extends ApiBase {
	public function execute() {
		$apiParams = $this->extractRequestParams();
		
		$user = $this->getUser();
		if ($user->getBlock()) {
			$this->dieBlocked( $user->getBlock() );
		}
		if ($user->pingLimiter('customlog')) {
			$this->dieWithError('apierror-ratelimited');
		}
		if (! $user->isAllowed('writecustomlogs')) {
			$this->dieWithError('apierror-permissiondenied');
		}
		
		$logType = $apiParams['logType'];
		$logEntry = new ManualLogEntry($logType, $logType);
		$logEntry->setTarget($this->getTitleFromTitleOrPageId($apiParams));
		$logEntry->setComment($apiParams['summary']);
		$logEntry->setPerformer($this->getUser());
		
		global $wgCustomLogsMax;
		$logParams = [];
		for ($i = 0; $i < $wgCustomLogsMax; $i++) {
			$value = $apiParams[self::getApiParamFromIndex($i)];
			$key = self::getMsgParamFromIndex($i);
			$logParams[$key] = $value;
		}
		
		$logEntry->setParameters($logParams);
		
		$logEntry->setTags($apiParams['tags']);
		
		$logId = $logEntry->insert();
		
		if ($apiParams['publish']) {
			$logEntry -> publish($logId);
		}
		$ret = [
			'logId' => $logId,
			'result' => 'Success!',
		];
		ApiResult::setIndexedTagName($ret, 'result');
		$this->getResult()->addValue(null, $this->getModuleName(), $ret);
	}
	
	private static function getApiParamFromIndex($i) {
		$index = "custom-param-" . strval($i);
		return $index;
	}
	
	private static function getMsgParamFromIndex($i) {
		$indexNumber = strval(4 + $i);
		$indexName = self::getApiParamFromIndex($i);
		$index = $indexNumber . "::" . $indexName;
		return $index;
	}
	
	public function mustBePosted() {
		return true;
	}
	
	public function isWriteMode() {
		return true;
	}
	
	public function getAllowedParams() {
		$logList = CustomLogCreator::getCustomLogList();
		global $wgCustomLogsMax;
		$paramList = [
			'logType' => [
				ApiBase::PARAM_TYPE => $logList,
			],
			'title' => null,
			'pageid' => [
				ApiBase::PARAM_TYPE => 'integer'
			],
			'summary' => null,
			'tags' => [
				ApiBase::PARAM_TYPE => 'tags',
				ApiBase::PARAM_ISMULTI => true,
			],
			'publish' => [
				ApiBase::PARAM_TYPE => 'boolean',
			]
		];
		
		for ($i = 0; $i < $wgCustomLogsMax; $i++) {
			$paramList[self::getApiParamFromIndex($i)] = [
				ApiBase::PARAM_HELP_MSG => 'apihelp-CustomLogs-param-custom-param'
			];
		}
		
		return $paramList;
	}
	
	public function needsToken() {
		return 'csrf';
	}
}