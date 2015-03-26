<?php $this->start('script');?>
<script type="text/javascript">
    $(function(){
        $('#quality-slider').slider({min: 56, max: 320, steps:[128, 192, 256], change: function(val){
            $('#SettingQuality').val(val);
            $('.quality div:last').text(val+'kb/s');
        }});
        $('#quality-slider').slider("value", $('#SettingQuality').val());
    });
</script>
<?php $this->end();?>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-8 col-xs-12">
        <div class="col-xs-12">
            <h3><?php echo __('Sonerezh settings'); ?></h3>
            <hr />
            <?php
            echo $this->Form->create('Setting');
            echo $this->Form->input('id');
            ?>
            <label for="SettingRootPath">
                <?php echo __('Music root directory'); ?>
            </label>
            <div id="root-path-input-group" class="input-group">
                <?php
                echo $this->Form->input('rootpath', array(
                    'type'  => 'text',
                    'label' => false,
                    'div'   => false,
                    'placeholder' => __('Music root directory'),
                    'after' => '<span class="input-group-btn"><button type="button" id="add-root-path-field" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i></button></span>'
                ));
                ?>
            </div>
            <small>
                <?php echo '<span class="help-block">Make sure Sonerezh can read this folder recursively. Current App folder is: '.APP.'</span>'; ?>
            </small>
            <?php

            echo $this->Form->input('enable_mail_notification', array(
                'type'  => 'checkbox',
                'label' => __('Enable mail notifications.'),
            ));
            ?>

            <small>
                <span class="help-block">
                    <?php echo __('Sonerezh can send an email on users creation to notify them.'); ?>
                    <?php echo $this->Html->link(
                        '<i class="glyphicon glyphicon-question-sign"></i>',
                        'https://www.sonerezh.bzh/docs/en/configuration.html#enable-mail-notification',
                        array('escape' => false, 'target' => 'blank', 'class' => 'no-ajax')
                    ); ?>
                </span>
            </small>


            <div class="panel <?php echo $avconv ? 'panel-default' : 'panel-danger'; ?>">
                <div class="panel-heading">
                    <h4 class="panel-title"><?php echo __('Automatic tracks conversion'); ?>
                </div>
                <div class="panel-body">
                    <?php if(!$avconv): ?>
                        <p class="text-danger">
                            <strong><?php echo __("The command 'avconv' is not available. Sonerezh cannot convert your tracks.") ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="help-block">
                            <small>
                                <?php echo __('If you have heterogeneous formats in your collection, and because your browser cannot read them all, Sonerezh can convert your tracks to MP3 or OGG/Vorbis before being played. The converted songs will be stored in the "Songs Cache".'); ?>
                            </small>
                        </p>
                        <div class="col-xs-12">
                            <div class="col-xs-6 separator-right text-right">
                                <h5><strong><?php echo __('Source format'); ?></strong></h5>
                                <?php echo $this->Form->input('from_mp3', array('type' => 'checkbox', 'label' => 'MPEG-1/2 Audio Layer 3 (MP3)')); ?>
                                <?php echo $this->Form->input('from_ogg', array('type' => 'checkbox', 'label' => 'Ogg Vorbis (OGG)')); ?>
                                <?php echo $this->Form->input('from_aac', array('type' => 'checkbox', 'label' => 'Advanced Audio Coding (AAC)', 'disabled' => 'disabled', 'checked' => 'checked')); ?>
                                <?php echo $this->Form->input('from_flac', array('type' => 'checkbox', 'label' => 'Free Lossless Audio Codec (FLAC)', 'disabled' => 'disabled', 'checked' => 'checked')); ?>
                            </div>
                            <div class="col-xs-6">
                                <h5><strong><?php echo __('Destination format'); ?></strong></h5>
                                <?php echo $this->Form->input('convert_to', array('type' => 'radio', 'options' => array('mp3' => 'MP3', 'ogg' => 'OGG'))); ?>
                                <h5><strong><?php echo __('Quality'); ?></strong></h5>
                                <?php echo $this->Form->input('quality', array('type' => 'hidden'));?>
                                <div class="row quality">
                                    <div class="col-xs-9"><div id="quality-slider"></div></div>
                                    <div class="col-xs-3"><?php echo $this->request->data['Setting']['quality'];?>kb/s</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php echo $this->Form->end(array('label' => __('Submit'), 'class' => 'btn btn-success pull-right')); ?>
        </div>

        <div class="col-xs-12">
            <h3><?php echo __('Database management'); ?></h3>
            <hr />
            <div class="col-sm-4 col-xs-12">
                <?php echo $this->Html->link(__('Database update'), array('controller' => 'songs', 'action' => 'import'), array('class' => 'btn btn-info clearfix center-block'));?>
            </div>
            <div class="col-sm-4 col-xs-12">
                <?php echo $this->Html->link(__('Clear the cache'), array('controller' => 'settings', 'action' => 'clear'), array('class' => 'btn btn-warning clearfix center-block'));?>
            </div>
            <div class="col-sm-4 col-xs-12">
                <?php echo $this->Form->postLink(__('Reset the database'), array('action' => 'truncate'), array('class' => 'btn btn-danger clearfix center-block'), __('Are you sure? All your songs and playlists will disappear!')); ?>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xs-12">
        <div class="col-xs-12">
            <h3><?php echo __('Support Sonerezh!'); ?></h3>
            <hr />
            <?php echo __('Help us to provide you the best music player! Make a donation.'); ?>

            <div class="row" style="margin-top: 20px;">
                <div class="col-xs-6 text-center">
                    <a href="https://flattr.com/submit/auto?user_id=Sonerezh&url=https%3A%2F%2Fwww.sonerezh.bzh&title=Sonerezh" target="_blank" class="no-ajax">
                        <?php echo $this->Html->image('flattr-badge-large.png', array('alt' => 'Flattr this', 'title' => 'Flattr this'));?>
                    </a>
                </div>
                <div class="col-xs-6 text-center">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="no-ajax">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="TWMJXGFUK8SXG">
                        <?php echo $this->Form->submit("btn_donate_SM.gif", array('name' => 'submit', 'alt' => 'PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !'));?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <h3><?php echo __('Sonerezh statistics'); ?></h3>
            <hr />
            <ul class="list-group" style="margin-bottom: 0;">
                <li class="list-group-item">
                    <span class="badge"><?php echo $stats['artists']; ?></span>
                    <?php echo __('Artists'); ?>
                </li>
                <li class="list-group-item">
                    <span class="badge"><?php echo $stats['albums']; ?></span>
                    <?php echo __('Albums'); ?>
                </li>
                <li class="list-group-item">
                    <span class="badge"><?php echo $stats['songs']; ?></span>
                    <?php echo __('Songs'); ?>
                </li>
                <li class="list-group-item">
                    <span class="badge"><?php echo $this->FileSize->humanize($stats['thumbCache']); ?></span>
                    <?php echo __('Thumbnails cache'); ?>
                </li>
                <li class="list-group-item">
                    <span class="badge"><?php echo $this->FileSize->humanize($stats['mp3Cache']); ?></span>
                    <?php echo __('Songs cache'); ?>
                </li>
            </ul>
            <p class="help-block text-right">
                <small><?php echo 'Sonerezh'.' '.SONEREZH_VERSION; ?> | <?php echo $this->Html->link('sonerezh.bzh', 'https://www.sonerezh.bzh', array('class' => 'no-ajax', 'target' => 'blank')); ?></small>
            </p>
        </div>
    </div>
</div>