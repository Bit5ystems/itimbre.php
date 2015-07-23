<?php

class iTimbre{
	private $service, $credentials, $testing, $debug, $endPoint, 
		$request, $retcode, $response, $processingTime, $source,
	 	$query
	;
	
	public $error=false
	;
	
	/**
	 * Constructor
	 * 
	 * @param boolean $testing Set if test or real stamp.
	 */
	public function __construct(boolean $testing=true){
		$this->testing=$testing;
		$this->query=new stdClass();
	}
	
	/**
	 * @param string $service | Desired service accordding to the defined in constant services. See constants clause for supported options.
	 * @param array $credentials | Desired credentials to use on service. Following next structures as match:
	 * <code>
	 * 	$ifactura_cretentials = array(
	 * 		'user' => 'user to authenticate with',
	 * 		'cuenta' => 'iFactura's account to connect',
	 * 		'pass' => 'password',
	 * 	);
	 * 
	 * 	$only_stamp_credentials = array(
	 * 		
	 * );
	 * </code> 
	 * @return boolean | False if error generated.
	 */
	public function auth(string $service, array $credentials){
		$this->service = $service;
		if (!call_user_func(array($this, "{$service}Auth"), $credentials)) return $this->error;
		return $this->stateTarget();
	}
	
	private function iFacturaAuth($credentials){
		if (!$this->checkParams($credentials, array('user','account','pass',))) return $this->error;

		$this->credentials = $credentials;
		return true;
	}
	
	public function checkParams($src, $reqParams=array(), $explainer=''){
		foreach ($src as $i){
			if (empty($reqParams[$i])){
				$this->error = 'Missing required key: ' . $i ." $explainer";
				return $this->error;
			}
		}
		return true;
	}
	
	private function stateTarget(){
		if (empty($this->service)){
			$this->error = 'Service not given yet';
			return false;
		}
		
		switch ($this->service){
			case self::IFACTURA_SERVICE:
				$this->urlTarget = $this->testing ? self::IF_WS_PRODUCTION_ENDPOINT : self::IF_WS_PRODUCTION_ENDPOINT;
				break;
			case self::WS_SERVICE:
				$this->urlTarget = $this->testing ? self::WS_TESTING_ENDPOINT : self::WS_PRODUCTION_ENDPOINT;
				
			default:
				$this->error = 'Unkwown service providen';
				return false;
		}
		
		return true;
	}
	
	
	
	/* iFactura service */
	public function handleiFacturaInvoice($data, $options){
		$this->source = $data;
		$clauses = array('invoice_data', 'conceptos', 'cliente');
		
		foreach ($clauses as $c){
			if (!call_user_func(array($this, "check_$c"))) return false;
		}
		
		$this->query->method = self::IF_INVOICE_METHOD;
		
		if (empty($options['pdf']) && $options['pdf']) $this->query->getPdf = true;
		return true;
	}
	
	private function check_cliente(){
		$req = array('rfc', 'nombre');
		if (!$this->checkParams($this->source['cliente'], $req, 'in cliente')) return false;
		$this->query->cliente = $this->source['cliente'];
		return true;
	}
	
	
	private function check_conceptos(){
		$req = array('unidad','cantidad','descripcion','valorUnitario','impuesto','porcentaje_imp');
		if (!$this->checkParams($this->source['conceptos'], $req, 'in conceptos')) return false;
		$this->query->conceptos = $this->source['conceptos'];
		return true;
	}
	
	private function check_invoice_data(){
		$req = array('numero_de_pago','cantidad_de_pagos','metodoDePago','tipoDeComprobante','Moneda','LugarExpedicion','RegimenFiscal',);
		if (!$this->checkParams($this->source['invoice_data'], $req, 'in invoice_data')) return false;
		$this->query->datos_factura = $this->source['invoice_data'];
		return true;
	}
	
	
	/*	Service communication	*/
	public function postQuery(){
		$this->endPoint = $this->handleServices();
		$this->response = self::curlMe($this->query, $target);
		
		call_user_func(array($this, 'handleResponse' . $this->service));
	}
	
	private function handleServices(){
		switch ($this->service){
			case self::IFACTURA_SERVICE:
				if ($this->testing) return self::IF_WS_SANDBOX_ENDPOINT;
				else return self::IF_WS_PRODUCTION_ENDPOINT;
		
			case self::WS_SERVICE:
				if ($this->testing) return self::WS_TESTING_ENDPOINT;
				else return self::WS_PRODUCTION_ENDPOINT;
		
			default:
				$this->error = 'Unkwown endpoint for providen service';
				return false;
		}
	}
	
	/* Generic cURL*/
	public static function curlMe($query, $target){
		$ch = curl_init($target);
		
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array ('q' => utf8_decode(json_encode($query))));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$result_encoded = curl_exec($ch);
		curl_close($ch);
		
	
		return json_decode($result_encoded);
	}
	
	const IF_INVOICE_METHOD = 'nueva_factura';
	
	const IFACTURA_SERVICE = 'iFactura';
	const WS_SERVICE = 'only_stamps';
	
	
	const WS_PRODUCTION_ENDPOINT = 'https://portalws.itimbre.com/itimbre.php';
	const WS_TESTING_ENDPOINT = 'https://portalws.itimbre.com/itimbreprueba.php';
	
	const IF_WS_PRODUCTION_ENDPOINT = 'https://facturacion.itimbre.com/service.php';
	const IF_WS_SANDBOX_ENDPOINT = 'https://sandbox.itimbre.com/service.php';
	
	

}