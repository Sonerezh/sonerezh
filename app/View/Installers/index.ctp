<?php echo $this->start('script'); ?>
<script>
    $(function(){
        $('#DBDatasource').selecter({
            label: "<?php echo __('Select a database type'); ?>"
        });
        $('#DBDatasource').change(function(){
            if($(this).val() == "Database/Sqlite"){
                $('.sqlite-optional').hide().find('input').removeAttr('required');
            }else{
                $('.sqlite-optional').show().find('input:not(#DBPrefix)').attr('required', 'required');
            }
        }).change();
    });
</script>
<?php echo $this->end(); ?>

<div class="col-xs-12" style="margin-bottom: 20px;">
    <div class="page-header">
        <h1><?php echo __('Sonerezh'); ?></h1>
    </div>
    <p>
        <?php echo __("Welcome on the Sonerezh's installation page. Just fill in the information below and you'll be on your way to listening to your favorite songs!"); ?>
    </p>

    <h2><?php echo __('Requirements'); ?></h2>
    <hr />

    <?php foreach($requirements as $requirement): ?>
        <div class="alert alert-<?php echo $requirement['label']; ?>">
            <?php echo $requirement['message']; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!$missing_requirements): ?>

    <h2><?php echo __('Database configuration'); ?></h2>
    <hr />

    <p>
        <?php echo __("Please provide the following information to allow Sonerezh to access its database."); ?> <span class="text-danger"><?php echo __('Note that if you are reinstalling Sonerezh, all your previous data will be lost.') ?></span>
    </p>


    <?php echo $this->Form->create(null, array(
        'class' => 'form-horizontal',
        'inputDefaults' => array(
            'label' => array('class' => 'col-sm-3 control-label'),
            'div' => 'form-group',
            'between' => '<div class="col-sm-9">',
            'after' => '</div>',
            'class' => 'form-control',
            'error' => array('attributes' => array('wrap' => 'div', 'class' => 'text-danger col-sm-offset-3 col-sm-9'))
        )
    )); ?>

    <div class="col-xs-8 col-xs-offset-2">
        <?php
        echo $this->Form->input('DB.datasource', array(
            'options'   => $available_drivers,
            'label'     => array('text' => __('Database type'), 'class' => 'col-sm-3 control-label', 'style' => 'padding-top: 20px;'),
            'required'
        ));
        ?>
        <div class="sqlite-optional">
            <?php
            echo $this->Form->input('DB.host', array('placeholder' => __('You can specify a non standard port if needed (127.0.0.1:1234)'), 'required'));
            ?>
        </div>
        <?php
        echo $this->Form->input('DB.database', array('placeholder' => __('Database name'), 'required'));
        ?>
        <div class="sqlite-optional">
            <?php
            echo $this->Form->input('DB.login', array('placeholder' => __('Database user login'), 'required'));
            echo $this->Form->input('DB.password', array('placeholder' => __('Database user password'), 'required'));
            echo $this->Form->input('DB.prefix', array('placeholder' => __('Leave empty if none'), 'label' => array('text' => __('Prefix (optional)'), 'class' => 'col-sm-3 control-label')));
            ?>
        </div>
    </div>

    <div class="clearfix"></div>


    <h2><?php echo __('Information needed'); ?></h2>
    <hr />

    <p>
        <?php echo __("Please provide the following information. Don't worry, you can always change these settings later."); ?>
    </p>

    <div class="col-xs-8 col-xs-offset-2">

        <?php
        echo $this->Form->input('User.email', array('placeholder' => 'john.doe@sonerezh.bzh', 'required'));
        echo $this->Form->input('User.password', array('placeholder' => __('Password'), 'label' => array('text' => __('Password (twice)'), 'class' => 'col-sm-3 control-label'), 'required'));
        echo $this->Form->input('User.confirm_password', array('placeholder' => __('Confirm your password'), 'type' => 'password', 'label' => array('text' => '', 'class' => 'col-sm-3 control-label'), 'required'));
        echo $this->Form->input('Setting.Rootpath.0.rootpath', array('placeholder' => '/home/jdoe/Music', 'label' => array('text' => 'Music folder', 'class' => 'col-sm-3 control-label'), 'after' => '<small><span class="help-block"><i class="glyphicon glyphicon-info-sign"></i> Current App folder is: '.APP.'</span></small>'));
        echo $this->Form->submit('Run!', array('class' => 'btn btn-success pull-right'));
        ?>
    </div>

    <?php echo $this->Form->end(); ?>
    <?php endif; ?>
</div>