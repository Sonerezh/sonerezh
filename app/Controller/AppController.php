<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::uses('BlowfishPasswordHasher', 'Controller/Component/Auth');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $helpers = array('Session', 'Form' => array('className' => 'BootstrapForm'), 'Html' => array('className' => 'AjaxHtml'), 'Image');
    public $components = array(
        'Session',
        'Cookie' => array(
            'name' => 'SonerezhCookie',
            'time' => '7 Days',
            'httpOnly' => true
        ),
        'Security' => array(
            'csrfExpires' => '+1 hour'
        ),
        'DebugKit.Toolbar',
        'Image',
        'Paginator',
        'Auth' => array(
            'authenticate' => array(
                'Form' => array(
                    'passwordHasher' => 'Blowfish',
                    'fields' => array('username' => 'email')
                )
            ),
            'authorize' => array('Controller'),
            'unauthorizedRedirect' => array('controller' => 'me', 'action' => 'index')
        ),
        'Flash'
    );

    public function isAuthorized($user) {
        return true;
    }

    public function beforeFilter() {

        if (isset($this->request->query['ajax'])) {
            unset($this->request->query['ajax']);
        }

        if ($this->request->is('ajax')) {
            $this->response->type("application/json");
            $this->layout = "ajax";
        } elseif ($this->request->params['controller'] != 'installers' && !$this->__isInstalled()) {
            if (DOCKER) {
                $this->redirect(array('controller' => 'installers', 'action' => 'docker'));
            } else {
                $this->redirect(array('controller' => 'installers', 'action' => 'index'));
            }
        } elseif ($this->request->params['controller'] == 'installers' && $this->__isInstalled()) {
            $this->Flash->info(__('Sonerezh is already installed. Remove or rename app/Config/database.php to run the installation again.'));
            $this->redirect(array('controller' => 'songs', 'action' => 'index'));
        }

        if ($this->__isInstalled() && !$this->Auth->user() && $this->Cookie->check('auth')) {
            $this->loadModel('User');
            $cookie = $this->Cookie->read('auth');
            $authCookie = explode(':', $cookie);
            $user = $this->User->find('first', array('conditions' => array('id' => $authCookie[0])));
            $passwordHasher = new BlowfishPasswordHasher();
            if ($passwordHasher->check($user['User']['email'], $authCookie[1]) && $passwordHasher->check($user['User']['password'], $authCookie[2])) {
                unset($user['User']['password']);
                $this->Auth->login($user['User']);
                $this->Cookie->write('auth', $this->Cookie->read('auth'));
            } else {
                $this->Cookie->delete('auth');
            }
        }

        if (!$this->request->is('ajax') && $this->Auth->user()) {
            $this->loadModel('Setting');
            $setting = $this->Setting->find('first', array('fields' => array('sync_token')));
            $this->set('sync_token', $setting['Setting']['sync_token']);
        }

        $this->__setLanguage();
    }

    public function redirect($url, $status = null, $exit = true) {
        if ($url == null && $status == 403 && $this->request->is('ajax')) {
            $url = $this->Auth->loginAction;
        }
        parent::redirect($url, $status, $exit);
    }

    /**
     * This function checks if Sonerezh is already installed on the server
     * @return bool
     */
    private function __isInstalled() {
        $installed = true;

        if (!file_exists(APP."Config".DS."database.php")) {
            $installed = false;
        }

        if ($installed) {
            App::uses('ConnectionManager', 'Model');
            try {
                ConnectionManager::getDataSource('default');
            } catch (Exception $connectionError) {
                $installed = false;
            }
        }

        // The docker image is packaged with database files
        // So we check if there is at least one admin user in the db
        if (DOCKER && $installed) {
            try {
                $this->loadModel('User');
                $admins = $this->User->find('count', array(
                    'conditions' => array('role' => 'admin')
                ));
                if ($admins < 1) {
                    $installed = false;
                }
            } catch (Exception $connectionError) {
                $installed = false;
            }
        }
        return $installed;
    }

    /**
     * This function set the application language according to the browser language and saves it to a cookie
     */
    private function __setLanguage() {
        // Check if the cookie is already set
        if ($this->Cookie->read('lang')) {
            Configure::write('Config.language', $this->Cookie->read('lang'));
            return;
        }

        // Get browser language
        $browser_lang = substr(($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
        switch ($browser_lang) {
            case 'fr':
                $locale = 'fra';
                break;
            case 'de':
                $locale = 'deu';
                break;
            default:
                $locale = 'eng';
                break;
        }

        $this->Cookie->write('lang', $locale);
        Configure::write('Config.language', $locale);
    }
}
