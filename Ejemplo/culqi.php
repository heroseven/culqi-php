<?php
    define('CULQI_SDK_VERSION', '1.0.0'); class UrlAESCipher { protected $key; protected $cipher = MCRYPT_RIJNDAEL_128; protected $mode = MCRYPT_MODE_CBC; function __construct($sp2705d8 = null) { $this->setBase64Key($sp2705d8); } public function setBase64Key($sp2705d8) { $this->key = base64_decode($sp2705d8); } private function spdf4c30() { if ($this->key != null) { return true; } else { return false; } } private function sp1472ad() { return mcrypt_create_iv(16, MCRYPT_RAND); } public function urlBase64Encrypt($sp3a4f46) { if ($this->spdf4c30()) { $sp8c3827 = mcrypt_get_block_size($this->cipher, $this->mode); $spdf44fb = UrlAESCipher::pkcs5_pad($sp3a4f46, $sp8c3827); $sp50cc0b = $this->sp1472ad(); return trim(UrlAESCipher::base64_encode_url($sp50cc0b . mcrypt_encrypt($this->cipher, $this->key, $spdf44fb, $this->mode, $sp50cc0b))); } else { throw new Exception('Invlid params!'); } } public function urlBase64Decrypt($sp3a4f46) { if ($this->spdf4c30()) { $spf25f51 = UrlAESCipher::base64_decode_url($sp3a4f46); $sp50cc0b = substr($spf25f51, 0, 16); $spa6a016 = substr($spf25f51, 16); return trim(UrlAESCipher::pkcs5_unpad(mcrypt_decrypt($this->cipher, $this->key, $spa6a016, $this->mode, $sp50cc0b))); } else { throw new Exception('Invlid params!'); } } public static function pkcs5_pad($spce0dee, $sp8c3827) { $spd11ad1 = $sp8c3827 - strlen($spce0dee) % $sp8c3827; return $spce0dee . str_repeat(chr($spd11ad1), $spd11ad1); } public static function pkcs5_unpad($spce0dee) { $spd11ad1 = ord($spce0dee[strlen($spce0dee) - 1]); if ($spd11ad1 > strlen($spce0dee)) { return false; } if (strspn($spce0dee, chr($spd11ad1), strlen($spce0dee) - $spd11ad1) != $spd11ad1) { return false; } return substr($spce0dee, 0, -1 * $spd11ad1); } protected function base64_encode_url($sp506369) { return strtr(base64_encode($sp506369), '+/', '-_'); } protected function base64_decode_url($sp506369) { return base64_decode(strtr($sp506369, '-_', '+/')); } } class Culqi { public static $llaveSecreta; public static $codigoComercio; public static $servidorBase = 'https://pago.culqi.com'; public static function cifrar($speb4710) { $sp2eff3c = new UrlAESCipher(); $sp2eff3c->setBase64Key(Culqi::$llaveSecreta); return $sp2eff3c->urlBase64Encrypt($speb4710); } public static function decifrar($speb4710) { $sp2eff3c = new UrlAESCipher(); $sp2eff3c->setBase64Key(Culqi::$llaveSecreta); return $sp2eff3c->urlBase64Decrypt($speb4710); } } class Pago { const URL_VALIDACION_AUTORIZACION = '/web/validar/'; const URL_ANULACION = '/anular/'; const URL_CONSULTA = '/consultar/'; const PARAM_COD_COMERCIO = 'codigo_comercio'; const PARAM_EXTRA = 'extra'; const PARAM_SDK_INFO = 'sdk'; const PARAM_NUM_PEDIDO = 'numero_pedido'; const PARAM_MONTO = 'monto'; const PARAM_MONEDA = 'moneda'; const PARAM_DESCRIPCION = 'descripcion'; const PARAM_COD_PAIS = 'cod_pais'; const PARAM_CIUDAD = 'ciudad'; const PARAM_DIRECCION = 'direccion'; const PARAM_NUM_TEL = 'num_tel'; const PARAM_INFO_VENTA = 'informacion_venta'; const PARAM_TOKEN = 'token'; const PARAM_VIGENCIA = 'vigencia'; private static function getSdkInfo() { return array('v' => CULQI_SDK_VERSION, 'lng_n' => 'php', 'lng_v' => phpversion(), 'os_n' => PHP_OS, 'os_v' => php_uname()); } public static function crearDatospago($sp1f9e9c, $sp4bad66 = null) { Pago::validateParams($sp1f9e9c); $sp40ad57 = Pago::getCipherData($sp1f9e9c, $sp4bad66); $sp17f9bd = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp40ad57); $sp2e826e = Pago::validateAuth($sp17f9bd); if (!empty($sp2e826e) && array_key_exists(Pago::PARAM_TOKEN, $sp2e826e)) { $sp00dd9c = array(Pago::PARAM_COD_COMERCIO => $sp2e826e[Pago::PARAM_COD_COMERCIO], Pago::PARAM_TOKEN => $sp2e826e[Pago::PARAM_TOKEN]); $sp2e826e[Pago::PARAM_INFO_VENTA] = Culqi::cifrar(json_encode($sp00dd9c)); } return $sp2e826e; } public static function consultar($spcbf43b) { $sp40ad57 = Pago::getCipherData(array(Pago::PARAM_TOKEN => $spcbf43b)); $sp1f9e9c = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp40ad57); return Pago::postJson(Culqi::$servidorBase . Pago::URL_CONSULTA, $sp1f9e9c); } public static function anular($spcbf43b) { $sp40ad57 = Pago::getCipherData(array(Pago::PARAM_TOKEN => $spcbf43b)); $sp1f9e9c = array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio, Pago::PARAM_INFO_VENTA => $sp40ad57); return Pago::postJson(Culqi::$servidorBase . Pago::URL_ANULACION, $sp1f9e9c); } private static function getCipherData($sp1f9e9c, $sp4bad66 = null) { $spd9481f = array_merge(array(Pago::PARAM_COD_COMERCIO => Culqi::$codigoComercio), $sp1f9e9c); if (!empty($sp4bad66)) { $spd9481f[Pago::PARAM_EXTRA] = $sp4bad66; } $spd9481f[Pago::PARAM_SDK_INFO] = Pago::getSdkInfo(); $sp06c739 = json_encode($spd9481f); return Culqi::cifrar($sp06c739); } private static function validateAuth($sp1f9e9c) { return Pago::postJson(Culqi::$servidorBase . Pago::URL_VALIDACION_AUTORIZACION, $sp1f9e9c); } private static function validateParams($sp1f9e9c) { if (!isset($sp1f9e9c[Pago::PARAM_MONEDA]) or empty($sp1f9e9c[Pago::PARAM_MONEDA])) { throw new InvalidParamsException('[Error] Debe existir una moneda'); } else { if (strlen(trim($sp1f9e9c[Pago::PARAM_MONEDA])) != 3) { throw new InvalidParamsException('[Error] La moneda debe contener exactamente 3 caracteres.'); } } if (!isset($sp1f9e9c[Pago::PARAM_MONTO]) or empty($sp1f9e9c[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] Debe existir un monto'); } else { if (is_numeric($sp1f9e9c[Pago::PARAM_MONTO])) { if (!ctype_digit($sp1f9e9c[Pago::PARAM_MONTO])) { throw new InvalidParamsException('[Error] El monto debe ser un número entero, no flotante.'); } } else { throw new InvalidParamsException('[Error] El monto debe ser un número entero.'); } } } private static function postJson($sp28053d, $sp1f9e9c) { $sp6a904d = array('http' => array('header' => "Content-Type: application/json\r\n" . "User-Agent: php-context\r\n", 'method' => 'POST', 'content' => json_encode($sp1f9e9c), 'ignore_errors' => true), 'ssl' => array('CN_match' => 'www.culqi.com')); $sp8a27ec = stream_context_create($sp6a904d); $sp2e826e = file_get_contents($sp28053d, false, $sp8a27ec); $sp05128b = Culqi::decifrar($sp2e826e); return json_decode($sp05128b, true); } } class InvalidParamsException extends Exception { }