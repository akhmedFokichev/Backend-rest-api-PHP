<?php

class ResponseError {
	
	// public $code;
	// public $errorTitle;
	// public $errorText;
	
	public function __construct(
		public string $code = "",
		public string $errorTitle = "",
		public string $errorText = ""
	) {}

	public function toJson() {
		return array(
			'code' => $this->code,
			'title' => $this->errorTitle,
			'content' => $this->errorText
		);
	}
	
}