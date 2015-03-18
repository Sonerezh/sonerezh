<table width="630" align="center" border="0">
    <tbody>
    <tr width="630">
        <td style="font-size: 24px;">
            <?php echo __('Reset your password'); ?>
        </td>
    </tr>
    <tr width="630">
        <td style="padding-top: 25px;">
            <?php echo __('Hi, you receive this email because you asked for reset your password on Sonerezh.'); ?>
            <?php echo __('Please follow this ').$this->Html->link(__('link'), array(
                    'controller' => 'users',
                    'action' => 'resetPassword',
                    '?' => array('t' => $token),
                    'full_base' => true
                )
            ).__(' or copy and paste the following URL in your browser: ').$this->Html->Url(array(
                    'controller' => 'users',
                    'action' => 'resetPassword',
                    '?' => array('t' => $token)
                ), true
            );
            ?>
        </td>
    </tr>
    </tbody>
</table>