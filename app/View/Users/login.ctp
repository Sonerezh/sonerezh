<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
	<?php echo $this->Form->create('User'); ?>
		<fieldset>
			<h2>Sonerezh - <?php echo __('Please Sign In'); ?></h2>
			<hr class="colorgraph"/>
            <?php echo $this->Form->input('email', array('placeholder' => __('Email Address'), 'required')); ?>
			<?php echo $this->Form->input('password', array('placeholder' => __('Password'), 'required')); ?>

            <?php if($settings['Setting']['enable_mail_notification']): ?>
			<span class="button-checkbox clearfix">
                <!-- Not implemented yet
				<button type="button" class="btn" data-color="info"><?php echo __('Remember Me'); ?></button>
				<input type="checkbox" name="remember_me" id="remember_me" checked="checked" class="hidden">
				-->
				<a href="#forgot-password" class="btn btn-link pull-right" data-toggle="modal"><?php echo __('Forgot Password?'); ?></a>
			</span>
            <?php endif; ?>

			<hr class="colorgraph" />
			<div class="row">
				<div class="col-xs-6 col-sm-6 col-md-6">
                    <?php echo $this->Form->submit(__('Sign In'), array('class' => 'btn btn-lg btn-success btn-block')); ?>
				</div>
			</div>
		</fieldset>
	<?php echo $this->Form->end(); ?>
</div>

<div class="modal fade" id="forgot-password" tabindex="-1" role="dialog" aria-labelledby="forgot-password-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create('User', array('action' => 'setResetPasswordToken')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Retrieve my password'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                echo $this->Form->input('email', array(
                        'placeholder' => __('Enter your email'),
                        'after' => '<span class="help-block"><small>'.__('If your account exists, you will receive an email explaining how to change your password.').'</small></span>')
                );
                ?>
            </div>
            <div class="modal-footer">
                <?php echo $this->Form->submit(__('Send'), array('class' => 'btn btn-success')); ?>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>