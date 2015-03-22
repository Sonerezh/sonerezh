<?php

App::uses('AppController', 'Controller');
App::uses('File', 'Utility');
App::uses('ConnectionManager', 'Model');
App::uses('SchemaShell', 'Console/Command');

/**
 * Class InstallationsController
 * Sonerezh installation controller.
 *
 * @property Installer $Installer
 */
class InstallersController extends AppController {

    var $uses = array();
    var $layout = 'installer';

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * This function deploys Sonerezh
     * It connects to MySQL / MariaDB with the provided credentials, tries to create the database and populates it.
     * The first users is also created here, with the administrator role, and the default settings are applied.
     */
    public function index() {
        $this->view = "index";
        $gd = extension_loaded('gd');
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$libavtools = shell_exec("where avconv") || shell_exec("where ffmpeg");//WIN
		} else {
			$libavtools = shell_exec("which avconv") || shell_exec("which ffmpeg");//NO WIN
		}
		
        $is_config_writable = is_writable(APP.'Config');
        $is_core_writable = is_writable(APP.'Config'.DS.'core.php');

        $this->set(compact('gd', 'libavtools', 'is_config_writable', 'is_core_writable'));

        $this->loadModel('User');
        $this->loadModel('Setting');
        $this->User->useTable = false;
        $this->Setting->useTable = false;
        if ($this->Toolbar) {
            unset($this->Toolbar->panels['sql_log']);
        }

        if ($this->request->is('get') && $is_core_writable) {
            // Update Security settings
            $core_config_file = new File(APP.'Config'.DS.'core.php');
            $core_config_file->replaceText(
                array(
                    Configure::read('Security.cipherSeed'),
                    Configure::read('Security.salt')),
                array(
                    $this->__generateCipherKey(),
                    $this->__generateSalt()
                )
            );
        }
        else if ($this->request->is('post')) {

            $db_config_array = $this->request->data['DB'];
            $db_config_array['datasource'] = 'Database/Mysql';
            $db_config_array['persistent'] = false;
            $db_config_array['encoding'] = 'utf8';

            // Write app/Config/database.php
            $db_config_file = new File(APP.'Config'.DS.'database.php');

            if ($db_config_file->create()) {
                $db_config_data = "<?php\n";
                $db_config_data .= "class DATABASE_CONFIG {\n";
                $db_config_data .= 'public $default = '.var_export($db_config_array, true).";\n";
                $db_config_data .= '}';

                $db_config_file->write($db_config_data);
            } else {
                $this->Session->setFlash(__('Unable to write configuration file.'), 'flash_error');
                return;
            }

            // Check database connexion
            try {
                $db_connection = ConnectionManager::getDataSource('default');
                $db_connection->begin();
                $db_connection->execute("SHOW TABLES;");
                $db_connection->commit();
            } catch (Exception $e) {
                $db_config_file->delete();
                $this->Session->setFlash(__('Could not connect to database'), 'flash_error');
                return;
            }

            // Populate Sonerezh database
            // Export schema
            $schema_shell = new SchemaShell();
            $schema_shell->params = array('connection' => 'default', 'file' => 'sonerezh.php', 'yes' => 1, 'name' => 'Sonerezh');
            $schema_shell->startup();
            $schema_shell->create();

            // Save first user and firsts settings
            $this->User->useTable = 'users';
            $this->Setting->useTable = 'settings';

            $this->request->data['User']['role'] = 'admin';

            // Enable auto-conversion if libav-tools is available
            $this->request->data['Setting']['enable_auto_conv'] = $libavtools;


            if ($this->request->data['User']['password'] != $this->request->data['User']['confirm_password']) {
                $user = false;
                $this->User->validationErrors["password"][] = __("Passwords do not match.");
            } else {
                $user = $this->User->save($this->request->data['User']);
            }
            $setting = $this->Setting->save($this->request->data['Setting']);

            if ($user && $setting) {
                $this->Session->setFlash(__('Installation successful!'), 'flash_success');
            } else {
                $this->Session->setFlash(__('Unable to save your data.'), 'flash_error');
                $db_config_file->delete();
                return;
            }

            $this->redirect(array('controller' => 'songs', 'action' => 'import'));
        }
    }


    private function __generateCipherKey() {
        return $this->__commonRandom('34567890', 40);
    }

    private function __generateSalt() {
        return $this->__commonRandom('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYXZ1234567890', 40);
    }

    private function __commonRandom($chars = '', $size = 15) {
        $hash = "";
        for ($i = 0; $i < $size; $i++) {
            $hash .= $chars[rand(0, strlen($chars)-1)];
        }
        return $hash;
    }

}