<div class="jumbotron" style="margin-top: 20px;">
    <h1><?php echo __('Hello!'); ?></h1>
    <hr>
    <p>
        <?php echo __('This is the new homepage of Sonerezh.'); ?><br />
        <?php echo __('It is empty for the moment, but I would like to get your suggestions on how to organize it. Feel free to take part of '); ?>
        <?php echo $this->Html->link(
            __('the discussion on GitHub'),
            'https://github.com/Sonerezh/sonerezh/issues/362',
            array('target' => '_blank')
        ); ?>
        <?php echo __(', the best ideas will be implemented in a future release.') ?><br /><br />
        <?php echo __('Thank you!'); ?>
    </p>
</div>