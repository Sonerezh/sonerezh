<div class="col-xs-12" style="margin-bottom: 20px;">
    <div class="page-header">
        <h1><?php echo __('Sonerezh on Docker'); ?></h1>
    </div>
    <p>
        <?php echo __("Welcome on the Sonerezh's installation page. Just fill in the information below and you'll be on your way to listening to your favorite songs!"); ?>
    </p>

    <h2><?php echo __('Information needed'); ?></h2>
    <hr />

    <p>
        <?php echo __("Please provide the following information. Don't worry, you can always change these settings later."); ?>
    </p>

    <div class="col-xs-8 col-xs-offset-2">

        <?php
        echo $this->Form->create(null, array(
            'class' => 'form-horizontal',
            'inputDefaults' => array(
                'label' => array('class' => 'col-sm-3 control-label'),
                'div' => 'form-group',
                'between' => '<div class="col-sm-9">',
                'after' => '</div>',
                'class' => 'form-control',
                'error' => array('attributes' => array('wrap' => 'div', 'class' => 'text-danger col-sm-offset-3 col-sm-9'))
            )
        ));
        echo $this->Form->input('User.email', array('placeholder' => 'john.doe@sonerezh.bzh', 'required'));
        echo $this->Form->input('User.password', array('placeholder' => __('Password'), 'label' => array('text' => __('Password (twice)'), 'class' => 'col-sm-3 control-label'), 'required'));
        echo $this->Form->input('User.confirm_password', array('placeholder' => __('Confirm your password'), 'type' => 'password', 'label' => array('text' => '', 'class' => 'col-sm-3 control-label'), 'required'));
        echo $this->Form->submit('Run!', array('class' => 'btn btn-success pull-right'));
        ?>
    </div>

    <?php echo $this->Form->end(); ?>
</div>