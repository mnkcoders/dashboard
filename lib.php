<?php

class Dashboard{
    /**
     * @var string
     */
    private $_path = '';
    /**
     * @var string
     */
    private $_url = '';
    /**
     * @var int
     */
    private $_chars = 20;
    /**
     * @var array
     */
    private $_apps = array();
    /**
     * @param string $path
     * @param int $char
     */
    protected function __construct( $path = '' , $char = 20) {
        $this->_path = $path;
        $this->_chars = $char;
        $this->_url = $this->input();
        $this->addApp('phpmyadmin', 'Php My Admin');
    }
    /**
     * @param string $name
     * @param string $title
     * @return \Dashboard
     */
    private function addApp($name = '', $title = ''){
        if(strlen($name) && !array_key_exists($name,$this->_apps)){
            $this->_apps[$name] = strlen($title) ? $title : $name;
        }
        return $this;
    }
    /**
     * @return string
     */
    private function input(){
        $url = filter_input(INPUT_SERVER, 'REQUEST_URI') ?? '';
        return preg_replace( '/^\/|\/$/' , '' , $url);
    }
    /**
     * @param string $path
     * @return \Dashboard
     */
    public static function create($path = '') {
        return strlen($path) ? new Dashboard($path) : null;
    }
    
    /**
     * @param string $name
     * @return string
     */
    public function __get($name) {
        switch(true){
            case preg_match('/^list_/', $name):
                return $this->__run(sprintf('list%s', ucfirst(substr($name, 5))),array());
            case preg_match('/^get_/', $name):
                return $this->__run(sprintf('get%s', ucfirst(substr($name, 4))));
            case preg_match('/^is_/', $name):
                return $this->__run(sprintf('is%s', ucfirst(substr($name, 3))),false);
            case preg_match('/^has_/', $name):
                return $this->__run(sprintf('has%s', ucfirst(substr($name, 4))),false);
            case preg_match('/^count_/', $name):
                return $this->__run(sprintf('count%s', ucfirst(substr($name, 6))),0);
            case preg_match('/^print_/', $name):
                $print = substr($name, 6);
                echo $this->__run(sprintf('get%s', ucfirst($print)),$print);
                return '';
            default:
                return '';
        }
    }
    /**
     * @param string $call
     * @param mixed $default
     * @return mixed
     */
    private function __run( $call , $default = '' ){
        return method_exists($this, $call) ? $this->$call() : $default;
    }
    /**
     * @return string
     */
    private function path() {
        return $this->_path;
    }
    /**
     * @return int
     */
    protected function countChars() {
        return $this->_chars;
    }
    /**
     * @return int
     */
    protected function countRoute() {
        return count($this->listRoute());
    }
    /**
     * @return int
     */
    protected function countFolders() {
        return !$this->isLib() ? count($this->listFolders()) : 0;
    }
    /**
     * @return bool
     */
    protected function hasRoute() {
        return $this->countRoute() && strlen($this->listRoute()[0]) > 0;
    }
    /**
     * @return bool
     */
    protected function hasFolders() {
        return $this->countFolders() > 0;
    }
    /**
     * @return bool
     */
    protected function isLib() {
        $root = $this->listRoute()[0] ?? '';
        return $root === $this->root(true);
    }
    /**
     * @return string
     */
    protected function getTitle() {
        return 'Dashboard';
    }
    /**
     * @return string
     */
    protected function getRoot(){
        return $this->base(true);
    }
    /**
     * @return string
     */
    private function getBase(){
        return $this->base();
    }
    /**
     * @return string
     */
    public function getUrl() {
        return $this->base(true);
    }
    /**
     * @return string
     */
    public function getCss() {
        return sprintf('%s/html/style.css',$this->getApp());
    }
    /**
     * @return array
     */
    public function listRoute(){
        return  explode('/', $this->getBase());

    }
    /**
     * @return array
     */
    protected function listFolders() {
        
        if($this->isLib()){
            return array();
        }
        $list = array_map(function($folder){
            return preg_replace('/\\\\/', '/', $folder);
        }, glob( $this->path(). '/*'));
        $root = $this->root();
        $folders = array();
        foreach ($list as $folder ){
            if(is_dir($folder) && $folder !== $root){
                $folders[] = basename($folder);
            }
        }
        return $folders;
        
        $folders = !$this->isLib() ?
                array_filter(glob( $this->path(). '/*'), 'is_dir') :
                array();
        return array_map( function($path){
            return basename($path);
        } ,$folders);
    }
    /**
     * @return array
     */
    protected function listApps() {
        return $this->_apps;
    }
    /**
     * @return string
     */
    private function getHost() {
        return 'http://localhost/';
    }
    /**
     * @param bool $localhost
     * @return string
     */
    private function base($localhost = false) {
        return $localhost ?
                $this->getHost() . $this->_url :
                $this->_url;
    }
    /**
     * @return string
     */
    private function getApp() {
        return $this->getHost() . 'lib/';
    }
    /**
     * @return string
     */
    private function root( $basename = false ) {
        $root = preg_replace('/\\\\/', '/', __DIR__);
        return $basename ? basename($root) : $root;
    }
    /**
     * @param string $view
     * @return bool
     */
    public function view($view = 'main') {
        $path = sprintf('%s/html/%s.php',$this->root(),$view);
        if(file_exists($path)){
            require $path;
            return true;
        }
        return false;
    }
}

    
    