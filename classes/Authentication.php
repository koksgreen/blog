<?php
    class Authentication{
        public static function generateCSRFToken(){
            if(isset($_SESSION['crsf_token'])) {
                $_SESSION['crsf_token'] = sodium_bin2hex(random_bytes(32));
            }
            return $_SESSION['crsf_toekn'];
        }

        public static function verifyCSRFToken($token){
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }

        public static function regenerateSession(){
            session_regenerate_id(true);
        }
    }