<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
	<?php echo $this->Form->create('User'); ?>
		<fieldset>
			<h2>Sonerezh - <?php echo __('Please Sign In'); ?></h2>
			<hr class="colorgraph"/>
            <?php echo $this->Form->input('email', array('placeholder' => __('Email Address'), 'required')); ?>
			<?php echo $this->Form->input('password', array('placeholder' => __('Password'), 'required')); ?>

            <!-- Not yet implemented
			<span class="button-checkbox">
				<button type="button" class="btn" data-color="info"><?php echo __('Remember Me'); ?></button>
				<input type="checkbox" name="remember_me" id="remember_me" checked="checked" class="hidden">
				<a href="#" class="btn btn-link pull-right"><?php echo __('Forgot Password?'); ?></a>
			</span>
			-->

			<hr class="colorgraph" />
			<div class="row">
				<div class="col-xs-6 col-sm-6 col-md-6">
                    <?php echo $this->Form->submit(__('Sign In'), array('class' => 'btn btn-lg btn-success btn-block')); ?>
				</div>
			</div>
		</fieldset>
	<?php echo $this->Form->end(); ?>
</div>