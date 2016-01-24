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
 * @package       app.View.Layouts.Email.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?php echo $title_for_layout; ?></title>
</head>
<body>
	<table width="630" align="center" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 10px;">
		<tbody>
		<tr width="630" height="3" align="center">
			<td style="background-color: #c4e17f;"></td>
			<td style="background-color: #f7fdca;"></td>
			<td style="background-color: #fecf71;"></td>
			<td style="background-color: #f0776c;"></td>
			<td style="background-color: #db9dbe;"></td>
			<td style="background-color: #c49cde;"></td>
			<td style="background-color: #669ae1;"></td>
			<td style="background-color: #62c2e4;"></td>
		</tr>
		</tbody>
	</table>
	<?php echo $this->fetch('content'); ?>
	<table width="630" align="center" cellpadding="0" cellspacing="0" border="0" style="margin-top: 10px;">
		<tbody>
		<tr width="630" height="3" align="center">
			<td style="background-color: #c4e17f;"></td>
			<td style="background-color: #f7fdca;"></td>
			<td style="background-color: #fecf71;"></td>
			<td style="background-color: #f0776c;"></td>
			<td style="background-color: #db9dbe;"></td>
			<td style="background-color: #c49cde;"></td>
			<td style="background-color: #669ae1;"></td>
			<td style="background-color: #62c2e4;"></td>
		</tr>
		</tbody>
	</table>
</body>
</html>