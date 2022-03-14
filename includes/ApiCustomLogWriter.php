<?php

use Wikimedia\ParamValidator\ParamValidator;

class ApiCustomLogWriter extends ApiBase {
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

		$logType = $apiParams['logtype'];
		$logEntry = new ManualLogEntry($logType, $logType);
		$logEntry->setTarget($this->getTitleFromTitleOrPageId($apiParams));
		$logEntry->setComment($apiParams['summary']);
		$logEntry->setPerformer($this->getUser());

		global $wgCustomLogsMaxCustomParams;
		$logParams = [];
		for ($i = 0; $i < $wgCustomLogsMaxCustomParams; $i++) {
			$value = $apiParams[self::getApiParamFromIndex($i)];
			$key = self::getMsgParamFromIndex($i);
			$logParams[$key] = $value;
		}

		$logEntry->setParameters($logParams);

		$logEntry->addTags($apiParams['tags']);

		$logId = $logEntry->insert();

		if ($apiParams['publish']) {
			$logEntry -> publish($logId);
		}
		$ret = [
			'logid' => $logId,
			'result' => 'Success!',
		];
		ApiResult::setIndexedTagName($ret, 'result');
		$this->getResult()->addValue(null, $this->getModuleName(), $ret);
	}

	const API_HELP_PREFIX = 'apihelp-customlogs-param-';
	const CUSTOM_PARAM_PREFIX = 'custom';

	private static function getApiParamFromIndex($i) {
		$index = self::CUSTOM_PARAM_PREFIX . strval($i + 1);
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
		global $wgCustomLogsMaxCustomParams;
		$paramList = [
			'logtype' => [
				ParamValidator::PARAM_TYPE => $logList,
			],
			'title' => null,
			'pageid' => [
				ParamValidator::PARAM_TYPE => 'integer'
			],
			'summary' => null,
			'tags' => [
				ParamValidator::PARAM_TYPE => 'tags',
				ParamValidator::PARAM_ISMULTI => true,
			],
			'publish' => [
				ParamValidator::PARAM_TYPE => 'boolean',
			]
		];

		$fallbackKey = self::API_HELP_PREFIX . self::CUSTOM_PARAM_PREFIX;

		for ($i = 0; $i < $wgCustomLogsMaxCustomParams; $i++) {
			$paramName = self::getApiParamFromIndex($i);
			$specificKey = self::API_HELP_PREFIX . $paramName;
			$message = wfMessageFallback($specificKey, $fallbackKey);
			$paramList[$paramName] = [
				ApiBase::PARAM_HELP_MSG => $message
			];
		}

		return $paramList;
	}

	public function needsToken() {
		return 'csrf';
	}
}
