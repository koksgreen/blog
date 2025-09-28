<?php
    class Authentication{
        public static function generateCSRFToken(){
            if(!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }

        public static function verifyCSRFToken($token){
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }

        public static function regenerateSession(){
            session_regenerate_id(true);
        }
    }
?>