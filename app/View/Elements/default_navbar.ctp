<ul class="nav navbar-nav">
    <li><?= $this->Html->link(__('Artists'), array('controller' => 'songs', 'action' => 'artists')); ?></li>
    <li><?= $this->Html->link(__('Albums'), array('controller' => 'songs', 'action' => 'albums')); ?></li>
    <li><?= $this->Html->link(__('Playlists'), array('controller' => 'playlists', 'action' => 'index')); ?></li>
</ul>
<form action="<?= $this->Html->url(array('controller' => 'songs', 'action' => 'search'));?>" class="navbar-form navbar-left hidden-sm hidden-xs" role="search">
    <div class="form-group search" >
        <input type="text" class="form-control search-input" placeholder="<?= __('Search'); ?>" name="q" >
    </div>
</form>
<ul class="nav navbar-nav navbar-right">
    <li class="hidden-lg hidden-md"><?= $this->Html->link('<i class="glyphicon glyphicon-search"></i>', array('controller' => 'songs', 'action' => 'search'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Search'), 'class' => 'nav-tooltips', 'escape' => false)); ?></li>
    <li><?= $this->Html->link('<i class="glyphicon glyphicon-log-out"></i>', array('controller' => 'users', 'action' => 'logout'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Logout'), 'class' => 'nav-tooltips no-ajax', 'escape' => false)); ?></li>
    <li>
        <?php echo $this->Html->link(
            $this->Html->image(
                $this->Image->avatar(AuthComponent::user(), 30),
                array('alt' => 'Gravatar image',
                    'class' => 'img-responsive nav-tooltips',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'bottom',
                    'title' => __('Hi :)')
                )
            ),
            array('controller' => 'users', 'action' => 'edit', AuthComponent::user('id')),
            array('class' => 'nav-avatar', 'escape' => false)
        );?>
    </li>
</ul>