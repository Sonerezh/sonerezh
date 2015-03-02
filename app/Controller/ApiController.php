<?php

App::uses('AppController', 'Controller');


/**
 * Class ApiController
 *
 * @property Song $Song
 */
class ApiController extends AppController {

    private $jsonVars = null;

    public function __construct($request = null, $response = null) {
        parent::__construct($request, $response);
        $this->modelClass = "Song";
        $this->modelKey = Inflector::underscore($this->modelClass);
    }

    public function beforeFilter() {
        $this->viewClass = "Json";
    }

    public function json($one, $two = null){
        parent::set($one, $two);
        $this->jsonVars = $this->viewVars;
    }

    public function beforeRender() {
        $this->set('_serialize', array_keys($this->jsonVars));
    }

}