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
        <?= $this->Html->css(array('bootstrap.min', 'login'));?>
        <?php

            echo $this->fetch('meta');
            echo $this->fetch('css');
        ?>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
                    <?php echo $this->Session->flash(); ?>
                </div>
            </div>
            <div class="row" id="content">
                <?php echo $this->fetch('content'); ?>
            </div>
        </div>
        <?php echo $this->Html->script(array(
            "jquery-2.1.0.min",
            "bootstrap.min"));?>
        <?php echo $this->fetch('script'); ?>
    </body>
</html>
