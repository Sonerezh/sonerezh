<div class="alert alert-danger">
    <?php if (!empty($params)) {
        echo h($message) . h($params[0]);
    } else {
        echo h($message);
    } ?>
</div>