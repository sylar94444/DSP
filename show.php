<?php
/*
class aes {
 
    // CRYPTO_CIPHER_BLOCK_SIZE 32
     
    private $_secret_key = 'default_secret_key';
     
    public function setKey($key) {
        $this->_secret_key = $key;
    }
     
    public function encode($data) {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td),MCRYPT_RAND);
        mcrypt_generic_init($td,$this->_secret_key,$iv);
        $encrypted = mcrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
         
        return $iv . $encrypted;
    }
     
    public function decode($data) {
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256,'',MCRYPT_MODE_CBC,'');
        $iv = mb_substr($data,0,32,'latin1');
        mcrypt_generic_init($td,$this->_secret_key,$iv);
        $data = mb_substr($data,32,mb_strlen($data,'latin1'),'latin1');
        $data = mdecrypt_generic($td,$data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
         
        return trim($data);
    }
}

	$enc_price = $_GET['c'];
	
	$aes = new aes();
	$aes->setKey('76c8b1e3370d4b38');
    
	//加密
	//$string = $aes->encode('string');
	// 解密
	$dec_price = $aes->decode($enc_price);
	//$dec_price = 'string';
*/

/* * *
 * AES_Encryption
 * This class allows you to easily encrypt and decrypt text in AES format
 * The class automatically determines whether you need 128, 192, or 256 bits
 * based on your key size. It handles multiple padding formats.
 * 
 * Dependencies:
 * This class is dependent on PHP's mcrypt extension and a class called padCrypt
 * 
 * Information about mcrypt extension is at:
 * http://php.net/mcrypt
 * 
 * 
 * common padding methods described at:
 * http://en.wikipedia.org/wiki/Padding_%28cryptography%29
 * 
 * -- AES_Encryption Information
 * 
 * Key Sizes:
 * 16 bytes = 128 bit encryption
 * 24 bytes = 192 bit encryption
 * 32 bytes = 256 bit encryption
 * 
 * Padding Formats:
 * ANSI_X.923
 * ISO_10126
 * PKCS7(PKCS5 compatible)
 * BIT
 * ZERO
 * 
 * The default padding method in this AES_Encryption class is ZERO padding
 * ZERO padding is generally OK for paddings in messages because 
 * null bytes stripped at the end of a readable message should not hurt
 * the point of the text. If you are concerned about message integrity, 
 * you can use PKCS7 instead
 * 
 * This class does not generate keys or vectors for you. You have to 
 * generate them yourself because you need to keep track of them yourself 
 * anyway in order to decrypt AES encryptions.
 * 
 * -- Example Usage:
 * //example 1:
 * $key 	= "bac09c63f34c9845c707228b20cac5e0";
 * $iv 		= "47c743d1b21de03034e0842352ae6b98";
 * $message = "Meet me at 11 o'clock behind the monument.";
 * 
 * $AES              = new AES_Encryption($key, $iv);
 * $encrypted        = $AES->encrypt($message);
 * $decrypted        = $AES->decrypt($encrypted);
 * $base64_encrypted = base64_encode($encrypted);
 * 
 * //example 2:
 * $key = 'HFGKQLCBPQMGMV7Q';
 * $aes = new AESEncryption($key, $initVector = '', $padding = 'PKCS7', $mode = 'ecb', $encoding = 'hex');
 * $text = 'hello world';
 * $enc = $aes->encrypt($text);
 * var_dump($key,$enc, $aes->decrypt($enc));
 * 
 * */

class AESEncryption {

    private $key, $initVector, $mode, $cipher, $encryption = null, $encoding = false;
    private $allowed_bits = array(128, 192, 256);
    private $allowed_modes = array('ecb', 'cfb', 'cbc', 'nofb', 'ofb');
    private $vector_modes = array('cbc', 'cfb', 'ofb');
    private $allowed_paddings = array(
	'ANSI_X.923' => 'ANSI_X923',
	'ISO_10126' => 'ISO_10126',
	'PKCS5' => 'PKCS5',
	'PKCS7' => 'PKCS7',
	'BIT' => 'BIT',
	'ZERO' => 'ZERO',
    );

    /*     * *
     * String $key        = Your secret key that you will use to encrypt/decrypt
     * String $initVector = Your secret vector that you will use to encrypt/decrypt if using CBC, CFB, OFB, or a STREAM algorhitm that requires an IV
     * String $padding    = The padding method you want to use. The default is ZERO (aka NULL byte) [ANSI_X.923,ISO_10126,PKCS7,BIT,ZERO]
     * String $mode       = The encryption mode you want to use. The default is cbc [ecb,cfb,cbc,stream,nofb,ofb]
     * */

    public function __construct($key, $initVector = '', $padding = 'ZERO', $mode = 'cbc', $encoding = false) {
	$mode = strtolower($mode);
	$padding = strtoupper($padding);
	$encoding = empty($encoding) ? $encoding : strtolower($encoding);

	if (!function_exists('mcrypt_module_open')) {
	    throw new Exception('The mcrypt extension must be loaded.');
	}

	$this->encryption = strlen($key) * 8;

	if (!in_array($this->encryption, $this->allowed_bits)) {
	    throw new Exception('The $key must be either 16, 24, or 32 bytes in length for 128, 192, and 256 bit encryption respectively.');
	}

	$this->key = $key;

	if (!in_array($mode, $this->allowed_modes)) {
	    throw new Exception('The $mode must be one of the following: ' . implode(', ', $this->allowed_modes));
	}

	if (!array_key_exists($padding, $this->allowed_paddings)) {
	    throw new Exception('The $padding must be one of the following: ' . implode(', ', $this->allowed_paddings));
	}

	$this->mode = $mode;
	$this->padding = $padding;
	$this->cipher = mcrypt_module_open('rijndael-128', '', $this->mode, '');
	$this->block_size = mcrypt_get_block_size('rijndael-128', $this->mode);
	$this->encoding = $encoding;

	//if in ecb mode, fill the init vector automatic
	if ($this->mode === 'ecb') {
	    $initVector = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->cipher), MCRYPT_RAND);
	} else {
	    if (strlen($initVector) != 16 && in_array($mode, $this->vector_modes)) {
		throw new Exception('The $initVector is supposed to be 16 bytes in for CBC, CFB, NOFB, and OFB modes.');
	    } elseif (!in_array($mode, $this->vector_modes) && !empty($initVector)) {
		throw new Exception('The specified encryption mode does not use an initialization vector. You should pass an empty string, zero, FALSE, or NULL.');
	    }
	}

	$this->initVector = $initVector;
    }

    private function hex2bin($hexdata) {
	$bindata = '';
	$length = strlen($hexdata);
	for ($i = 0; $i < $length; $i += 2) {
	    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
	}
	return $bindata;
    }

    /*     * *
     * String $text = The text that you want to encrypt
     * */

    public function encrypt($text) {
	mcrypt_generic_init($this->cipher, $this->key, $this->initVector);
	$encrypted_text = mcrypt_generic($this->cipher, $this->pad($text, $this->block_size));
	mcrypt_generic_deinit($this->cipher);
	if ($this->encoding === false) {
	    return $encrypted_text;
	} else {
	    return ($this->encoding === 'base64') ? base64_encode($encrypted_text) : bin2hex($encrypted_text);
	}
    }

    /*     * *
     * String $text = The text that you want to decrypt
     * */

    public function decrypt($text) {
	mcrypt_generic_init($this->cipher, $this->key, $this->initVector);
	if ($this->encoding === false) {
	    $decrypted_text = mdecrypt_generic($this->cipher, $text);
	} else {
	    $decrypted_text = ($this->encoding === 'base64') ? mdecrypt_generic($this->cipher, base64_decode($text)) : mdecrypt_generic($this->cipher, $this->hex2bin($text));
	}
	mcrypt_generic_deinit($this->cipher);
	return $this->unpad($decrypted_text);
    }

    /*     * *
     * Use this function to export the key, init_vector, padding, and mode
     * This information is necessary to later decrypt an encrypted message
     * */

    public function getConfiguration() {
	return array(
	    'key' => $this->key,
	    'init_vector' => $this->initVector,
	    'padding' => $this->padding,
	    'mode' => $this->mode,
	    'encoding' => $this->encoding,
	    'encryption' => $this->encryption . ' Bit',
	    'block_size' => $this->block_size,
	);
    }

    /**
     * magic pad method
     * @param type $text
     * @param type $block_size
     * @return type
     */
    private function pad($text, $block_size) {
	return call_user_func_array(array(__CLASS__, 'pad_' . $this->allowed_paddings[$this->padding]), array($text, $block_size));
    }

    /**
     * magic unpad method
     * @param type $text
     * @return type
     */
    private function unpad($text) {
	return call_user_func_array(array(__CLASS__, 'unpad_' . $this->allowed_paddings[$this->padding]), array($text));
    }

    public static function pad_ISO_10126($data, $block_size) {
	$padding = $block_size - (strlen($data) % $block_size);

	for ($x = 1; $x < $padding; $x++) {
	    mt_srand();
	    $data .= chr(mt_rand(0, 255));
	}

	return $data . chr($padding);
    }

    public static function unpad_ISO_10126($data) {
	$length = ord(substr($data, -1));
	return substr($data, 0, strlen($data) - $length);
    }

    public static function pad_ANSI_X923($data, $block_size) {
	$padding = $block_size - (strlen($data) % $block_size);
	return $data . str_repeat(chr(0), $padding - 1) . chr($padding);
    }

    public static function unpad_ANSI_X923($data) {
	$length = ord(substr($data, -1));
	$padding_position = strlen($data) - $length;
	$padding = substr($data, $padding_position, -1);

	for ($x = 0; $x < $length; $x++) {
	    if (ord(substr($padding, $x, 1)) != 0) {
		return $data;
	    }
	}

	return substr($data, 0, $padding_position);
    }

    public static function pad_PKCS7($data, $block_size) {
	$padding = $block_size - (strlen($data) % $block_size);
	$pattern = chr($padding);
	return $data . str_repeat($pattern, $padding);
    }

    public static function unpad_PKCS7($data) {
	$pattern = substr($data, -1);
	$length = ord($pattern);
	$padding = str_repeat($pattern, $length);
	$pattern_pos = strlen($data) - $length;

	if (substr($data, $pattern_pos) == $padding) {
	    return substr($data, 0, $pattern_pos);
	}

	return $data;
    }

    public static function pad_BIT($data, $block_size) {
	$length = $block_size - (strlen($data) % $block_size) - 1;
	return $data . "\x80" . str_repeat("\x00", $length);
    }

    public static function unpad_BIT($data) {
	if (substr(rtrim($data, "\x00"), -1) == "\x80") {
	    return substr(rtrim($data, "\x00"), 0, -1);
	}

	return $data;
    }

    public static function pad_ZERO($data, $block_size) {
	$length = $block_size - (strlen($data) % $block_size);
	return $data . str_repeat("\x00", $length);
    }

    public static function unpad_ZERO($data) {
	return rtrim($data, "\x00");
    }

    public function __destruct() {
	mcrypt_module_close($this->cipher);
    }
}

	$key = '76c8b1e3370d4b38';
	//$key = 'HFGKQLCBPQMGMV7Q';
	$enc_price = $_GET['c'];
	$aes = new AESEncryption($key, $initVector = '76c8b1e3370d4b38', $padding = 'PKCS7', $mode = 'cbc', $encoding = 'hex');
	$dec_price = $aes->decrypt($enc_price);
	//var_dump($dec_price);
	
	$ip = $_SERVER["REMOTE_ADDR"];
    $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$referer = $_SERVER['HTTP_REFERER'];
	$ua = $_SERVER['HTTP_USER_AGENT'];
	
    $dbconnect = mysql_connect('localhost','dsp_admin','dsp_admin') or die('Could not connect: ' . mysql_error());
    mysql_select_db("dsp") or die("Unable to select database!");
    
	$sql = "INSERT INTO `dsp`.`mgtv_show` (`show_time`, `enc_price`, `dec_price`, `ip`, `url`, `referer`, `ua`) VALUES (CURRENT_TIMESTAMP, '".$enc_price."','".$dec_price."','".$ip."','".$url."','".$referer."', '".$ua."')"; 
    mysql_query($sql) or die("Error in insert: ".mysql_error());
    
    mysql_close($dbconnect);
?>
