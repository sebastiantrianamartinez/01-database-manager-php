<?php
    define(ROOT, dirname(__FILE__, 3));

    class responser {
        public static function systemResponse($statusCode, $statusMessage, $data){
            return [
                "status" => $statusCode,
                "message" => $statusMessage,
                "data" => $data,
                "signature" => "systemresponse"
            ];
        }
        public static function preformedHttpResponse($objectResposne){
            $objectResposne["signature"] = "httpresponse";
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($objectResposne["status"]);
            echo json_encode($objectResposne);
        }
    
        public static function httpResponse($code, $message, $data){
            $response = [
                "status" => $code,
                "message" => $message,
                "data" => $data,
                "signature" => "httpresponse"
            ];
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($code);
            echo json_encode($response);
        }
    }