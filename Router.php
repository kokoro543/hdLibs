<?php
namespace App\Libs;

class  Router{
    const PARAM_NAME = "page";
    const HOME_PAGE = "index";
    const INDEX_PAGE = "index";

    public static $sourcePath;
    public function __construct($sourcePath=""){
        if($sourcePath){
            self::$sourcePath = $sourcePath;
        }
    }

    public function getGET($name){
        if($name !== NULL){
            return $_GET[$name]??NULL;
        }
    }

    public function getPost($name){
        if($name !== NULL){
            return $_GET[$name]??NULL;
        }
    }

    public function router(){
        $url = $this->getGet(self::PARAM_NAME);
        if(!is_string($url) || !$url || $url == self::INDEX_PAGE){
            $url = self::HOME_PAGE;
        }
        $path = self::$sourcePath.DIRECTORY_SEPARATOR.$url.".php";
        if(file_exists($path)){
            return require_once($path);
        }else{
            return $this->page404();
        }
    }

    public function page404(){
        echo "404 Page Not Found!";die();
    }

}