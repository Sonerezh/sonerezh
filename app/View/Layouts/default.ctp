<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset(); ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo $title_for_layout; ?>
    </title>
    <?= $this->Html->meta('icon'); ?>
    <?= $this->Html->css(array('bootstrap.min', 'jquery.fs.selecter.min', 'pace', 'notify', 'slider', 'style'));?>
    <?php
    echo $this->Html->script("lazyload.min");
    echo $this->fetch('meta');
    echo $this->fetch('css');
    ?>
    <script id="queue-tr" type="text/html">
        <tr>
            <td class="truncated-name title"></td>
            <td class="truncated-name hidden-xs hidden-sm artist"></td>
            <td class="truncated-name visible-lg album"></td>
            <td class="text-right playtime-cell">
                <span class="song-playtime"></span>
                <span class="glyphicon glyphicon-play song-controls action-play" title="<?= __('Play'); ?>"></span><span class="glyphicon glyphicon-pause song-controls action-pause" title="<?= __('Pause'); ?>"></span><span class="glyphicon glyphicon-remove song-controls action-remove-from-queue" title="<?= __('Remove from queue'); ?>"></span>
            </td>
        </tr>
    </script>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation" id="main-nav-bar">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle Navigation</span> <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <?= $this->Html->link("Sonerezh", array('controller' => 'songs', 'action' => 'index'), array('class' => 'navbar-brand'));?>
        </div>
        <div class="collapse navbar-collapse">
            <?php if(null !== AuthComponent::user('role') && AuthComponent::user('role') == 'admin'){
                echo $this->element('admin_navbar');
            }else{
                echo $this->element('default_navbar');
            }?>
        </div>
    </div>
</nav>
<!-- Player -->
<div class="navbar navbar-default navbar-fixed-top navbar-player">
    <div class="container">
        <!-- Play/Pause buttons -->
        <div class="col-md-2 col-xs-3">
            <ul class="player-controls">
                <li><span class="glyphicon glyphicon-backward" id="backward"></span></li>
                <li class="play"><span class="glyphicon glyphicon-play" id="play"></span></li>
                <li><span class="glyphicon glyphicon-forward" id="forward"></span></li>
            </ul>
        </div>
        <!-- Volume control -->
        <div class="col-md-2 hidden-xs hidden-sm">
            <ul class="player-controls volume">
                <li class="volumeicon"><span class="glyphicon glyphicon-volume-up" id="mute"></span></li>
                <li class="volumebar"><div id="volume"></div></li>
            </ul>
        </div>
        <!-- Current playing -->
        <div class="col-md-6 col-xs-6">
            <?= $this->Html->image("no-cover.png", array('class' => "song-cover hidden-xs")); ?>
            <div class="song-infos truncated-name">
                <span class="song-name"></span>
                -
                <span class="song-artist"></span>
            </div>
            <ul class="timebar">
                <li><span class="badge badge-timer currentTime"></span></li>
                <li class="bar"><div id="timebar"></div></li>
                <li><span class="badge badge-timer totalTime"></span></li>
            </ul>
        </div>
        <!-- Playlist management -->
        <div class="col-md-2 col-xs-2">
            <ul class="player-controls">
                <li><span class="glyphicon glyphicon-th-list" id="queue-button"></span></li>
                <li><span class="glyphicon glyphicon-repeat" id="queue-repeat"></span></li>
                <li><span class="glyphicon glyphicon-random" id="queue-shuffle"></span></li>
            </ul>
        </div>
    </div>
</div>

<!-- Queue -->
<section class="queue" id="queue">
    <div class="queue-wrapper">
        <div class="current-queue">
            <div class="current-queue-inner">
                <div class="container">
                    <div class="col-xs-12">
                        <!-- If queue is empty... -->
                        <div id="alert-empty-queue" class="alert alert-info">
                            <?= __('Queue is empty.'); ?>
                        </div>
                        <div id="queue-list" style="display:none">
                            <div class="col-xs-12">
                                <h4><?= __('Queue').'&nbsp;<small><i><span class="queue-size">'.'0</span> '.__('Songs').'</i></small>'; ?></h4>
                                <table class="table table-striped table-condensed table-hover table-album">
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="flash">
    <?php echo $this->Session->flash(); ?>
</div>

<div class="container">
    <div class="row" id="content">
        <?php echo $this->fetch('content'); ?>
    </div>
</div>
<script type="text/javascript">
    var baseurl = "<?= $this->request->base; ?>";
</script>
<?php echo $this->Html->script(array(
    "jquery-2.1.0.min",
    "Player",
    "player-nav",
    "navigation",
    "pace.min",
    "bootstrap.min",
    "jquery.slider",
    "jquery.scroll",
    "jquery.fs.selecter.min"));?>
<?php echo $this->fetch('script'); ?>
</body>
</html>
