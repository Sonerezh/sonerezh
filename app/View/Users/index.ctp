<?php echo $this->start('script'); ?>
<script>
    $(function(){
        $('#UserRole').selecter({
           label: "<?php echo __('Select a role'); ?>"
        });
    });
</script>
<?php echo $this->end(); ?>

<div class="col-lg-12">
    <h3>
        <?php echo __('Users'); ?><br />
        <small><?php echo __('Share your music with your friends !'); ?></small>
    </h3>
    <hr />

    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-striped table-condensed table-hover">
                <thead>
                <tr>
                    <th></th>
                    <th><?php echo __('Email / Login'); ?></th>
                    <th class="text-center"><?php echo __('Role'); ?></th>
                    <th class="text-center"><?php echo __('Management'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td style="width: 42px;">
                            <?php 
                            if (isset($user['User']['gravatar'])) {
                                echo $this->Html->image($user['User']['gravatar'].'?s=32');
                            } else {
                                echo $this->Html->image($this->Image->resizedPath($user['User']['avatar'], 32, 32), array('width' => '32px', 'height' => '32px'));
                            }
                            ?>
                        </td>
                        <td style="vertical-align: middle;"><?php echo $user['User']['email']; ?></td>
                        <?php if($user['User']['role'] == 'admin'){ ?>
                            <td class="text-center" style="vertical-align: middle;"><span class="label label-primary"><?php echo __('Administrator'); ?></span></td>
                        <?php }else{ ?>
                            <td class="text-center" style="vertical-align: middle;"><span class="label label-info"><?php echo __('Listener'); ?></span></td>
                        <?php } ?>
                        <td class="text-center">
                            <?php echo $this->Html->link(
                                '<i class="glyphicon glyphicon-edit"></i>',
                                array('action' => 'edit', $user['User']['id']),
                                array('class' => 'btn btn-sm btn-info', 'escape' => false)
                            ); ?>
                            <?php echo $this->Form->postLink(
                                '<i class="glyphicon glyphicon-trash"></i>',
                                array('action' => 'delete', $user['User']['id']),
                                array('class' => 'btn btn-sm btn-danger', 'escape' => false),
                                __('Are you sure ?')
                            ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#newUserModal"><?php echo __('New User'); ?></button>
        </div>
    </div>
</div>

<div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create('User', array('action' => 'add')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Create a user'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                echo $this->Form->input('email', array(
                    'placeholder' => __('Enter an email'),
                    'after' => '<span class="help-block"><small>'.__('We also use email for avatar detection if no avatar is uploaded.').'</small></span>')
                );
                echo $this->Form->input('password', array('placeholder' => __('Choose a password')));
                echo $this->Form->input('confirm_password', array('type' => 'password', 'placeholder' => __('Confirm password')));
                echo $this->Form->input('role', array(
                    'options'   => array('admin' => __('Administrator'), 'listener' => __('Listener')),
                    'label'     => __('Select a role')
                ));
                ?>
            </div>
            <div class="modal-footer">
                <?php echo $this->Form->submit(__('Create'), array('class' => 'btn btn-success')); ?>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>