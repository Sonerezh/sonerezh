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
            'unauthorizedRedirect' => array('controller' => 'songs', 'action' => 'index')
        )
    );

    public function isAuthorized($user) {
        return true;
    }

    public function beforeFilter() {
        if ($this->request->is('ajax')) {
            $this->response->type("application/json");
            $this->layout = "ajax";
        } elseif ($this->request->params['controller'] != 'installers' && !$this->__isInstalled()) {
            $this->redirect(array('controller' => 'installers', 'action' => 'index'));
        } elseif ($this->request->params['controller'] == 'installers' && $this->__isInstalled()) {
            $this->Session->setFlash(__('Sonerezh is already installed. Remove or rename app/Config/database.php to run the installation again.'), 'flash_info');
            $this->redirect(array('controller' => 'songs', 'action' => 'index'));
        }
    }

    public function beforeRender() {
        parent::beforeRender();
        if (isset($this->request->query['ajax'])) {
            unset($this->request->query['ajax']);
        }
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
        return $installed;
    }
}
