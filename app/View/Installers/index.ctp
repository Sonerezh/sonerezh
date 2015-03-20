<div class="col-xs-12" style="margin-bottom: 20px;">
    <div class="page-header">
        <h1><?php echo __('Sonerezh'); ?></h1>
    </div>
    <p>
        <?php echo __("Welcome on the Sonerezh's installation page. Just fill in the information below and you'll be on your way to listening to your favorite songs!"); ?>
    </p>

    <h2><?php echo __('Requirements'); ?></h2>
    <hr />
    <!-- Check for PHP-GD -->
    <?php if ($gd): ?>
        <div class="alert alert-success">
            <?php echo __('PHP GD is installed!'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <?php echo __('PHP GD is missing.'); ?>
        </div>
    <?php endif; ?>

    <!-- Check for lib-avtools -->
    <?php if ($libavtools): ?>
        <div class="alert alert-success">
            <?php echo __('libav-tools (avconv) is installed!'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <?php echo __('libav-tools (avconv) is missing. Sonerezh will not be able to convert your tracks.'); ?>
        </div>
    <?php endif; ?>

    <!-- Check if APP/Config is writable -->
    <?php if ($is_config_writable): ?>
        <div class="alert alert-success">
            <?php echo __('I can write the configuration in '.APP.'Config'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <?php echo __('I cannot write the configuration in '.APP.'Config. Please verify access rights on that folder.'); ?>
        </div>
    <?php endif; ?>

    <!-- Check if APP/Config/core is writable -->
    <?php if ($is_core_writable): ?>
        <div class="alert alert-success">
            <?php echo __('I can write in the core configuration file ('.APP.'Config'.DS.'core.php)'); ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <?php echo __('I cannot write in the core configuration file ('.APP.'Config'.DS.'core.php). Please verify access rights on that file.'); ?>
        </div>
    <?php endif; ?>

    <h2><?php echo __('Database configuration'); ?></h2>
    <hr />

    <p>
        <?php echo __("Please provide the following information to allow Sonerezh to access to its MySQL database."); ?> <span class="text-danger"><?php echo __('Note that if you are reinstalling Sonerezh, all your previous data will be lost.') ?></span>
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
        echo $this->Form->input('DB.host', array('placeholder' => __('Database host (generally localhost)')));
        echo $this->Form->input('DB.database', array('placeholder' => __('Database name')));
        echo $this->Form->input('DB.login', array('placeholder' => __('Database user login')));
        echo $this->Form->input('DB.password', array('placeholder' => __('Database user password')));
        echo $this->Form->input('DB.prefix', array('placeholder' => __('Leave empty if none'), 'label' => array('text' => __('Prefix (optionnal)'), 'class' => 'col-sm-3 control-label')));
        ?>
    </div>

    <div class="clearfix"></div>


    <h2><?php echo __('Information needed'); ?></h2>
    <hr />

    <p>
        <?php echo __("Please provide the following information. Don't worry, you can always change these settings later."); ?>
    </p>

    <div class="col-xs-8 col-xs-offset-2">

        <?php
        echo $this->Form->input('User.email', array('placeholder' => 'john.doe@sonerezh.bzh'));
        echo $this->Form->input('User.password', array('placeholder' => __('Password'), 'label' => array('text' => __('Password (twice)'), 'class' => 'col-sm-3 control-label')));
        echo $this->Form->input('User.confirm_password', array('placeholder' => __('Confirm your password'), 'type' => 'password', 'label' => array('text' => '', 'class' => 'col-sm-3 control-label')));
        echo $this->Form->input('Setting.rootpath', array('placeholder' => '/home/jdoe/Music', 'label' => array('text' => 'Music folder', 'class' => 'col-sm-3 control-label'), 'after' => '<p class="help-block">Current App folder is: '.APP.'</p>'));

        if ($gd && $is_config_writable && $is_core_writable) {
            echo $this->Form->submit('Run!', array('class' => 'btn btn-success pull-right'));
        } else {
            echo '<button class="btn btn-danger pull-right" disabled>'.__('Missing requirements').'</button>';
        }

        ?>
    </div>

    <?php echo $this->Form->end(); ?>
</div>