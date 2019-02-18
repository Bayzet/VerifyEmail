<?php

namespace lib;

class Verify
{

    public function verifyEmail($email)
    {
        $expEmail = explode("@", $email);
        print_r($expEmail);
        $username = $expEmail[0];
        $domain = $expEmail[1];

        $result = $this->verifyDomain($domain);

        if (!getmxrr($domain, $mxhosts)){
            echo "На адрес {$email} отправка почты невозможна"; 
        }else{
            echo "На адрес {$email} отправка почты возможна"; 
        }
    }

    private function verifyDomain($domain){
        $result = dns_get_record($domain);
        return $result;
    }
}