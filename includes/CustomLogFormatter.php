<?php

class CustomLogFormatter extends LogFormatter {
	protected function getActionMessage() {
		$message = $this->msg($this->getMessageKey());
		if (! $message->exists()) {
			$entry = $this->entry;
			$logName = $entry->getSubtype();
			$msgText = $this->msg('customlogs-nomessage')->plain();
			$message = new RawMessage(str_replace('%NAME%',$logName,$msgText));
		}
		$message->params($this->getMessageParameters());
		return $message;
	}
}