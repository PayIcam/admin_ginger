<?php 

include('class/httpful.phar');

class Cas{
    protected $url;
    protected $timeout;
    
    public function __construct($url, $timeout=10){
        $this->url = $url;
        $this->timeout = $timeout;
    }
    
    public function authenticate($ticket, $service){
        $r = \Httpful\Request::get($this->getValidateUrl($ticket, $service))
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
	    
	$user = trim(str_replace("\n", "", $r->raw_body));
        // Log::warning("AuthenticationFailure : ($ticket, $service)".$r->raw_body."\n".$r->body);
        try {
            $xml = new SimpleXMLElement(trim(str_replace("\n", "", $r->raw_body)));
            $namespaces = $xml->getNamespaces();
            $serviceResponse = $xml->children($namespaces['cas']);
            $user = $serviceResponse->authenticationSuccess->user;
            $authFailed = $serviceResponse->authenticationFailure;
            if ($authFailed) {
                $attributes = $authFailed->attributes();
                echo ("AuthenticationFailure : ".$attributes['code']." ($ticket, $service)");
            }
        } catch (\Exception $e) {
            $user = trim(str_replace("\n", "", $r->raw_body));
        }
        if ($user) {
            return (string)$user; // cast simplexmlelement to string
        } else {
            echo "Cas return is weird : '{".$r->raw_body."}'";
        }
    }

    public function logout(){
        $r = \Httpful\Request::get($this->url."logout")
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
        $r->body = str_replace("\n", "", $r->body);
        try {
            $xml = new SimpleXMLElement($r->body);
            return true;
        }catch (Exception $e) {
            return false;
        }
    }
    
    public function getValidateUrl($ticket, $service){
        return $this->url."serviceValidate?ticket=".urlencode($ticket)."&service=".urlencode($service);
    }
}
