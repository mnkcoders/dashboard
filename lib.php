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
     * @var bool
     */
    private $_scanapps = false;
    /**
     * @var array
     */
    private $_apps = array();
    /**
     * @var array
     */
    private $_excluded = array();
    /**
     * @param string $path
     * @param bool $scan
     * @param int $char
     */
    protected function __construct( $path = '' , $scan = false, $char = 20) {
        $this->_path = $path;
        $this->_chars = $char;
        $this->_url = $this->input();
        $this->addApp('phpmyadmin', 'Php My Admin');
        //$this->addApp('server.php', 'Server');
        
        if($scan){
            $this->scanApps();
        }
    }
    /**
     * @param string $name
     * @param string $title
     * @return \Dashboard
     */
    public function addApp($name = '', $title = ''){
        if(strlen($name) && !array_key_exists($name,$this->_apps)){
            $this->_apps[$name] = strlen($title) ? $title : $name;
        }
        return $this;
    }
    /**
     * @return \Dashboard
     */
    private function scanApps(){
        $list = array_filter(glob( $this->path(). '/*'),function($file){
            return filetype($file) === 'file'
                && preg_match('/.php$/', strtolower($file))
                    && basename($file) !== 'index.php';
        });
        foreach ($list as $file ){
            $app = basename($file);
            $name = preg_replace('/_/',' ',explode('.', $app)[0]);
            $this->addApp($app, $name);
        }
        return $this;
    }

    /**
     * @param string $list
     * @return \Dashboard
     */
    public function exclude(array $list = array()){
        foreach($list as $folder ){
            $this->_excluded[] = $folder;
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
     * @param bool $scan
     * @param int $chars
     * @return \Dashboard
     */
    public static function create($path = '',$scan  = false , $chars = 20) {
        return strlen($path) ? new Dashboard($path,$scan,$chars) : null;
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

        $folders = array();
        $dir = glob( $this->path(). '/*');
        foreach ($dir as $file ){
            $folder = preg_replace('/\\\\/', '/', $file);
            $name =  basename($folder);
            if(is_dir($folder)){
                if(!$this->excluded($name) && $folder !== $this->root()){
                    $folders[] = $name;
                }
            }
        }
        return $folders;
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
     * @return bool
     */
    private function excluded($folder){
        return in_array($folder,$this->_excluded);
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

    
    