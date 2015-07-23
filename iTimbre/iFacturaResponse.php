<?php
class iFacturaResponse {

	private $responseObj, $result, $error, $retcode
	;

	public function __construct(stdClass $response) {
		$this->responseObj = $response;
		$this->retcode = $this->responseObj->result->retcode;
		$this->setResult();
	}

	public function setResult() {
		if ($this->responseObj->result->retcode == self::CODE_OK)
			$this->result = true;
		else {
			$this->result = false;
			$this->error = $this->responseObj->result->error;
		}
		
		return $this->result;
	}

	public function getError() {
		return array ('error' => $this->error,'retcode' => $this->retcode 
		);
	}

	public function getPDF($encoded = true) {
		if (!empty($this->responseObj->result->pdfBase64))
			return $encoded ? $this->responseObj->result->pdfBase64 : base64_decode(
				$this->responseObj->result->pdfBase64);
		
		return false;
	}

	public function getXML() {
		if (!empty($this->responseObj->result->data))
			return base64_decode($this->responseObj->result->data);
		
		return false;
	}

	public function getRFC() {
		if (!empty($this->responseObj->result->RFC))
			return base64_decode($this->responseObj->result->RFC);
		
		return false;
	}

	public function getuser() {
		if (!empty($this->responseObj->result->user))
			return base64_decode($this->responseObj->result->user);
		
		return false;
	}

	public function getretcode() {
		if (!empty($this->responseObj->result->retcode))
			return base64_decode($this->responseObj->result->retcode);
		
		return false;
	}

	public function getUUID() {
		if (!empty($this->responseObj->result->UUID))
			return base64_decode($this->responseObj->result->UUID);
		
		return false;
	}

	public function getstampdate() {
		if (!empty($this->responseObj->result->stampdate))
			return base64_decode($this->responseObj->result->stampdate);
		
		return false;
	}

	public function getrefID() {
		if (!empty($this->responseObj->result->refID))
			return base64_decode($this->responseObj->result->refID);
		
		return false;
	}

	public function getacuse() {
		if (!empty($this->responseObj->result->acuse))
			return base64_decode($this->responseObj->result->acuse);
		
		return false;
	}

	public function getclient_email() {
		if (!empty($this->responseObj->result->client_email))
			return base64_decode($this->responseObj->result->client_email);
		
		return false;
	}

	public function getemail_result() {
		if (!empty($this->responseObj->result->email_result))
			return base64_decode($this->responseObj->result->email_result);
		
		return false;
	}

	public function getemail_error() {
		if (!empty($this->responseObj->result->email_error))
			return base64_decode($this->responseObj->result->email_error);
		
		return false;
	}
	
	/* Response handlign */
	const CODE_ERROR = -1;
	const CODE_OK = 1;
	const CODE_PROTOCOL_ERROR = 5;
	const CODE_IVALID_ACCOUNT_OR_CREDENTIALS = 10;
	const CODE_RESPONSE_NOT_RECEIVED = 100;
	const CODE_STAMP_ERROR = 101;
	const CODE_NO_STAMPS = 199;
}