<?php

App::uses('Component', 'Controller');

class CheckCmdComponent extends Component{

	public function is_shell_exec_available($cmd) {
		if (in_array('shell_exec', explode(',',ini_get('disable_functions')))){
            return shell_exec($cmd);
        }
        return false;
	}
}