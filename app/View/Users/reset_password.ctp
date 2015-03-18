<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
    <?php echo $this->Form->create('User'); ?>
    <fieldset>
        <h2>Sonerezh - <?php echo __('Reset your password'); ?></h2>
        <hr class="colorgraph"/>
        <p>
            <?php echo __('You asked for reset the password for the account ').'<code>'.$user['User']['email'].'</code>.'; ?>
            <?php echo __('You can now choose a new password.'); ?>
        </p>
        <?php echo $this->Form->input('password', array('placeholder' => __('Email Address'), 'required', 'label' => __('New Password'))); ?>
        <?php echo $this->Form->input('confirm_password', array('placeholder' => __('Password'), 'required', 'type' => 'password', 'label' => __('Confirm the new password'))); ?>
        <hr class="colorgraph" />
        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <?php echo $this->Form->submit(__('Reset my password'), array('class' => 'btn btn-lg btn-success btn-block')); ?>
            </div>
        </div>
    </fieldset>
    <?php echo $this->Form->end(); ?>
</div>