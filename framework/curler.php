<?php 
class HTTPRequest 
{ 
    var $_fp;        // HTTP socket 
    var $_url;        // full URL 
    var $_host;        // HTTP host 
    var $_protocol;    // protocol (HTTP/HTTPS) 
    var $_uri;        // request URI 
    var $_port;        // port 
    
    // scan url 
    function _scan_url() 
    { 
        $req = $this->_url; 
        
        $pos = strpos($req, '://'); 
        $this->_protocol = strtolower(substr($req, 0, $pos)); 
        
        $req = substr($req, $pos+3); 
        $pos = strpos($req, '/'); 
        if($pos === false) 
            $pos = strlen($req); 
        $host = substr($req, 0, $pos); 
        
        if(strpos($host, ':') !== false) 
        { 
            list($this->_host, $this->_port) = explode(':', $host); 
        } 
        else 
        { 
            $this->_host = $host; 
            $this->_port = ($this->_protocol == 'https') ? 443 : 80; 
        } 
        
        $this->_uri = substr($req, $pos); 
        if($this->_uri == '') 
            $this->_uri = '/'; 
    } 
    
    // constructor 
    function HTTPRequest($url) 
    { 
        $this->_url = $url; 
        $this->_scan_url(); 
    } 
    
    // download URL to string 
    function DownloadToString() 
    { 
        $crlf = "\r\n"; 
        
        // generate request 
        $req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf 
            .    'Host: ' . $this->_host . $crlf 
            .    $crlf; 
        
        // fetch 
        $this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port); 
        fwrite($this->_fp, $req); 
        while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp)) 
            $response .= fread($this->_fp, 1024); 
        fclose($this->_fp); 
        
        // split header and body 
        $pos = strpos($response, $crlf . $crlf); 
        if($pos === false) 
            return($response); 
        $header = substr($response, 0, $pos); 
        $body = substr($response, $pos + 2 * strlen($crlf)); 
        
        // parse headers 
        $headers = array(); 
        $lines = explode($crlf, $header); 
        foreach($lines as $line) 
            if(($pos = strpos($line, ':')) !== false) 
                $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1)); 
        
        // redirection? 
        if(isset($headers['location'])) 
        { 
            $http = new HTTPRequest($headers['location']); 
            return($http->DownloadToString($http)); 
        } 
        else 
        { 
            return($body); 
        } 
    } 
} 
function curl ($method,$url, $data = null)
{
	$ch  = curl_init();
	//$timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
 //    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
	// curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	// curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $data = curl_exec($ch);
    curl_close($ch);
	var_dump($data);
	return $data;
}

function json_post ($url, $data = null)
{
		$curl  = curl_init();

		if($params){
			$fields_string = http_build_query($params);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        $data = curl_exec($curl);
    	curl_close($curl);
		return $data;
}

function curl_get ($url, $params = null)
{
        if(is_array($params)){
             $url  .= '?' . http_build_query($params);;
        }
       
		$curl  = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT ,1);
        $data = curl_exec($curl);
    	curl_close($curl);
		return $data;
}

function stream_get($url , $data = array()){
    $c_data = array('http' =>
                    array(
                        'method'  => 'GET',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $data
                    )
    );
    $context  = stream_context_create($c_data);
    $result = @@file_get_contents($url, false, $context);
    return $result;
}

function stream_post($url , $data = array()){
    $c_data = array('http' =>
                    array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $data
                    )
    );
    $context  = stream_context_create($c_data);
    $result = @@file_get_contents($url, false, $context);
    return $result;
}
function httpGet($url)
{
	$r = new HttpRequest($url, HttpRequest::METH_GET);
	//$r->addQueryData(array('category' => 3));
	try {
	    $r->send();
	    if ($r->getResponseCode() == 200) {
	        return  $r->getResponseBody();
	    }
	} catch (HttpException $ex) {
	    echo $ex;
	}
}
function json_get2 ($url, $data = null)
{
	$resp = httpGet($url);
	return json_decode($resp,true);
}

function json_get ($url, $data = null)
{
	$resp = curl_get($url,$data);
	return json_decode($resp,true);
}


function json_stream_get ($url, $data = null)
{
    $resp = stream_get($url);
    return json_decode($resp,true);
}

?>