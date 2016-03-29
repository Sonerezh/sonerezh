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
        $requirements = array();
        $missing_requirements = false;

        $gd = extension_loaded('gd');

        if ($gd) {
            $requirements['gd'] = array('label' => 'success', 'message' => __('PHP GD is available and loaded.'));
        } else {
            $requirements['gd'] = array('label' => 'danger', 'message' => __('PHP GD is missing.'));
            $missing_requirements = true;
        }

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$libavtools = shell_exec("where avconv") || shell_exec("where ffmpeg");//WIN
		} else {
			$libavtools = shell_exec("which avconv") || shell_exec("which ffmpeg");//NO WIN
		}

        if ($libavtools) {
            $requirements['libavtools'] = array('label' => 'success', 'message' => __('libav-tools (avconv) is installed!'));
        } else {
            $requirements['libavtools'] = array('label' => 'warning', 'message' => __('libav-tools (avconv) is missing. Sonerezh will not be able to convert your tracks.'));
        }

        $pdo_drivers = PDO::getAvailableDrivers();
        $available_drivers = array(); // Used to load options on the view
        $drivers = array('mysql', 'pgsql', 'sqlite');

        if (empty($pdo_drivers)) {
            $requirements['pdo_drivers'] = array('label' => 'danger', 'message' => __('At least one PDO driver must be installed to run Sonerezh (mysql, pgsql or sqlite)'));
            $missing_requirements = true;
        } else {
            foreach ($drivers as $driver) {
                if (in_array($driver, $pdo_drivers)) {
                    $requirements[$driver] = array('label' => 'success', 'message' => $driver . ' ' .  __('driver is installed.'));

                    switch ($driver) {
                        case 'mysql':
                            $available_drivers['Database/Mysql'] = 'MySQL';
                            break;
                        case 'pgsql':
                            $available_drivers['Database/Postgres'] = 'PostgreSQL';
                            break;
                        case 'sqlite':
                            $available_drivers['Database/Sqlite'] = 'SQLite';
                            break;
                    }

                } else {
                    $requirements[$driver] = array('label' => 'warning', 'message' => $driver . ' ' . __('is required if you want to use Sonerezh with ') . $driver);
                }
            }
        }

        $is_config_writable = is_writable(APP.'Config');

        if ($is_config_writable) {
            $requirements['conf'] = array('label' => 'success', 'message' => APP . 'Config ' . __('is writable'));
        } else {
            $requirements['conf'] = array('label' => 'danger', 'message' => APP . 'Config ' . __('is not writable'));
            $missing_requirements = true;
        }

        $is_core_writable = is_writable(APP.'Config'.DS.'core.php');

        if ($is_core_writable) {
            $requirements['core'] = array('label' => 'success', 'message' => APP . 'Config' . DS . 'core.php ' . __('is writable'));
        } else {
            $requirements['core'] = array('label' => 'danger', 'message' => APP . 'Config' . DS . 'core.php ' . __('is not writable'));
            $missing_requirements = true;
        }

        $this->set(compact('requirements', 'missing_requirements', 'available_drivers'));

        $this->loadModel('User');
        $this->loadModel('Setting');
        $this->User->useTable = false;
        $this->Setting->useTable = false;
        if ($this->Toolbar) {
            unset($this->Toolbar->panels['sql_log']);
        }

        if ($this->request->is('post')) {

            $datasources = array('Database/Mysql', 'Database/Postgres', 'Database/Sqlite');

            if (in_array($this->request->data['DB']['datasource'], $datasources)) {
                if (isset($this->request->data['DB']['host'])) {
                    $db_fqdn = explode(':', $this->request->data['DB']['host']);
                    $this->request->data['DB']['host'] = $db_fqdn[0];

                    if (isset($db_fqdn[1])) {
                        $this->request->data['DB']['port'] = $db_fqdn[1];
                    }
                }

                $db_config_array = $this->request->data['DB'];
                $db_config_array['persistent'] = false;
                $db_config_array['encoding'] = 'utf8';
            } else {
                $this->Flash->error(__('Wrong datasource.'));
                return;
            }

            // Write app/Config/database.php
            $db_config_file = new File(APP.'Config'.DS.'database.php');

            if ($db_config_file->create()) {
                $db_config_data = "<?php\n";
                $db_config_data .= "class DATABASE_CONFIG {\n";
                $db_config_data .= 'public $default = '.var_export($db_config_array, true).";\n";
                $db_config_data .= '}';
                $db_config_file->write($db_config_data);
            } else {
                $this->Flash->error(__('Unable to write configuration file.'));
                return;
            }

            if ($this->request->data['DB']['datasource'] == 'Database/Sqlite') {
                $sqlite_path = $this->request->data['DB']['database'];

                // Create SQlite database file if it does not exist
                if (!file_exists($sqlite_path)) {
                    $sqlite_file = new File($sqlite_path);
                    if (!$sqlite_file->create()) {
                        $this->Flash->error(__('Unable to create the SQlite database file.'));
                        return;
                    }
                } elseif (!is_file($sqlite_path)) {
                    $this->Flash->error(__('This is not a regular file: '), array(
                        'params' => array($sqlite_path)
                    ));
                    return;
                }
            }

            // Check database connection
            try {
                $db_connection = ConnectionManager::getDataSource('default');
                $db_connection->connect();
            } catch (Exception $e) {
                $db_config_file->delete();
                $this->Flash->error(__('Could not connect to database'));
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
            $setting = $this->Setting->saveAssociated($this->request->data['Setting']);

            if ($user && $setting) {
                $this->Flash->success(__('Installation successful!'));
            } else {
                $this->Flash->error(__('Unable to save your data.'));
                $db_config_file->delete();
                return;
            }

            $core_config_file = new File(APP.'Config'.DS.'core.php');
            $core_config_file->replaceText(
                array(
                    Configure::read('Security.cipherSeed'),
                    Configure::read('Security.salt')
                ),
                array(
                    $this->__generateCipherKey(),
                    $this->__generateSalt()
                )
            );
            $this->Cookie->destroy();

            $this->redirect(array('controller' => 'songs', 'action' => 'import'));
        }
    }

    /**
     * This function is used to install Sonerezh on Docker
     */
    public function docker() {
        $this->loadModel('User');
        $this->loadModel('Setting');
        $this->User->useTable = false;
        $this->User->useTable = false;
        if ($this->Toolbar) {
            unset($this->Toolbar->panels['sql_log']);
        }

        if ($this->request->is('post')) {
            // Check database connection
            try {
                $db_connection = ConnectionManager::getDataSource('default');
                $db_connection->connect();
            } catch (Exception $e) {
                $this->Flash->error(__('Could not connect to the database'));
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

            // The first user is an admin
            $this->request->data['User']['role'] = 'admin';
            // Enable auto-conversion
            $this->request->data['Setting']['enable_auto_conv'] = true;
            // Force music path to /music
            $this->request->data['Setting']['Rootpath'] = array(array('rootpath' => '/music'));

            if ($this->request->data['User']['password'] != $this->request->data['User']['confirm_password']) {
                $user = false;
                $this->User->validationErrors["password"][] = __("Passwords do not match.");
            } else {
                $user = $this->User->save($this->request->data['User']);
            }
            $setting = $this->Setting->saveAssociated($this->request->data['Setting']);

            if ($user && $setting) {
                $this->Flash->success(__('Installation successful!'));
            } else {
                $this->Flash->error(__('Unable to save your data.'));
                return;
            }

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
            $this->Cookie->destroy();

            $this->redirect(array('controller' => 'songs', 'action' => 'import'));
        }
    }

    private function __generateCipherKey() {
        return $this->__commonRandom('1234567890', 40);
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
