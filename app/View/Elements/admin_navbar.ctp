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
    <li><?= $this->Html->link('<i class="glyphicon glyphicon-refresh"></i>', array('controller' => 'songs', 'action' => 'import'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Database update'), 'class' => 'nav-tooltips', 'escape' => false)); ?></li>
    <li><?= $this->Html->link('<i class="glyphicon glyphicon-cog"></i>', array('controller' => 'settings', 'action' => 'index'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Settings'), 'class' => 'nav-tooltips', 'escape' => false)); ?></li>
    <li><?= $this->Html->link('<i class="glyphicon glyphicon-user"></i>', array('controller' => 'users', 'action' => 'index'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Users'), 'class' => 'nav-tooltips', 'escape' => false)); ?></li>
    <li><?= $this->Html->link('<i class="glyphicon glyphicon-log-out"></i>', array('controller' => 'users', 'action' => 'logout'), array('data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Logout'), 'class' => 'nav-tooltips no-ajax', 'escape' => false)); ?></li>
    <li>
        <?php echo $this->Html->image($this->Image->avatar(AuthComponent::user(), 30), array('alt' => 'Avatar image', 'class' => 'img-responsive nav-tooltips', 'style' => 'padding: 10px 15px;', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => __('Hi :)')));?>
    </li>
</ul>