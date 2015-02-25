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
            echo $this->Form->input('rootpath', array('label' => __('Music root directory'), 'placeholder' => __('Music root directory')));
            ?>
            <span class=" offs help-block"><?php echo __('Make sure Sonerezh can read this folder recursively.'); ?></span>
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
                                <?php echo $this->Form->input('from_wma', array('type' => 'checkbox', 'label' => 'Windows Media Audio (WMA)', 'disabled' => 'disabled', 'checked' => 'checked')); ?>
                            </div>
                            <div class="col-xs-6">
                                <h5><strong><?php echo __('Destination format'); ?></strong></h5>
                                <?php echo $this->Form->input('convert_to', array('type' => 'radio', 'options' => array('mp3' => 'MP3', 'ogg' => 'OGG'))); ?>
                                <h5><strong><?php echo __('Quality'); ?></strong></h5>
                                <?php echo $this->Form->input('quality', array('type' => 'hidden'));?>
                                <div class="row quality">
                                    <div class="col-xs-9"><div id="quality-slider" ></div></div>
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
                        <img src="//api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0">
                    </a>
                </div>
                <div class="col-xs-6 text-center">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="no-ajax">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="TWMJXGFUK8SXG">
                        <input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
                        <img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
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