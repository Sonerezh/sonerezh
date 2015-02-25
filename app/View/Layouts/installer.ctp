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
    <?= $this->Html->css(array('bootstrap.min'));?>
    <?php
    echo $this->fetch('meta');
    echo $this->fetch('css');
    ?>
    <style>
        #flash .alert {
            margin: 20px 0 0 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div id="flash">
        <?php echo $this->Session->flash(); ?>
    </div>
    <div class="row" id="content">
        <?php echo $this->fetch('content'); ?>
    </div>
</div>
<script type="text/javascript">
    var baseurl = "<?= $this->request->base; ?>";
</script>
<?php echo $this->Html->script(array('jquery-2.1.0.min', 'bootstrap.min'));?>
<?php echo $this->fetch('script'); ?>
</body>
</html>
