<?php
/**
 *
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
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
[{
    "css": <?= json_encode($this->fetch('css'));?>,
    "js": <?= json_encode($this->fetch('script'));?>
},
{
    "title": "<?= $title_for_layout;?>",
    "url": "<?= $this->request->here(); ?>"
},
{
    "flash": <?= json_encode($this->Session->flash());?>,
    "html": <?= json_encode($this->fetch('content'));?>
}]
