<?php echo $this->start('script');?>
<script>
    $(function(){
        $('#UserRole').selecter({
            label: "<?php echo __('Select a role'); ?>"
        });
    });
</script>
<?php echo $this->end();?>
<div class="col-xs-12">
    <h3>
        <?php if($user['User']['id'] == AuthComponent::user('id')){
            echo __('Edit your account');
        }else{
            echo __('Edit a user');
        } ?>
    </h3>
    <hr />

    <?php echo $this->Form->create('User', array('type' => 'file')); ?>

    <div class="row">
        <div class="col-md-7 col-xs-12">
            <?php
            echo $this->Form->input('id', array('type' => 'hidden'));
            echo $this->Form->input('email', array(
                'placeholder' => __('Enter an email'),
                'after' => '<span class="help-block">'.__('We also use email for avatar detection if no avatar is uploaded.').'</span>'
            ));
            echo $this->Form->input('password', array('placeholder' => __('Choose a password'), 'label' => __('New password'), 'required' => false));
            echo $this->Form->input('confirm_password', array('type' => 'password', 'placeholder' => __('Confirm new password'), 'label' => __('Confirm new password'), 'required' => false));
            if(AuthComponent::user('role') == 'admin'){
                echo $this->Form->input('role', array(
                    'options'   => array('admin' => __('Administrator'), 'listener' => __('Listener')),
                    'label'     => __('Select a role')
                ));
            }
            ?>
        </div>

        <div class="col-md-5 col-xs-12">
            <div class="well">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="avatar-container">

                            <?php echo $this->Html->image($this->Image->avatar($user['User'], 188));?>

                            <?php if(!empty($user['User']['avatar'])){ ?>
                                <div class="avatar-remover avatar-selector">
                                    <?= $this->Html->link('
                                        <div class="avatar-remover avatar-remover-icon">
                                            <i class="glyphicon glyphicon-remove"></i>
                                        </div>
                                        <div class="avatar-remover avatar-selector-label">
                                            '.__('Remove Avatar').'
                                        </div>',
                                        array('action' => 'deleteAvatar', $user['User']['id']),
                                        array('class' => 'avatar-remover avatar-remover-link', 'escape' => false),
                                        __('You avatar will be removed, are you sure?'));
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <?= __('You can upload an avatar here or change it at ').'<a href="https://gravatar.com" target="_blank">gravatar.com</a>'; ?>
                        <hr />
                        <?= $this->Form->input('avatar', array('type' => 'file', 'required' => false, 'label' => false, 'class' => false, 'style' => 'max-width: 100%;')); ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?= $this->Form->submit(__('Save Changes'), array('class' => 'btn btn-success pull-right')); ?>
            </div>
        </div>
    </div>
    <?= $this->Form->end(); ?>
</div>
