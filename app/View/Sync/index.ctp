<style type="text/css">
    .fade-enter-active, fade-leave-active {
        transition: opacity 500ms;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>
<div class="col-lg-12" id="app">
    <h3><?php echo __('Update the music collection'); ?></h3>
    <hr />
    <p>
        <?php echo __('This page allows you to update the Sonerezh\'s database.'); ?>
        <?php echo __('The process is divided in three steps:'); ?>
    </p>
    <ul>
        <li>
            <?php echo __('Import new content'); ?>
            <transition name="fade">
                <span class="label label-info" v-if="loading === false">
                    {{ scan.to_import }} <?php echo __('files found'); ?>
                </span>
            </transition>
            <transition name="fade">
                <span class="label label-info" v-if="imported > 0">
                    {{ imported }} <?php echo __('imported'); ?>
                </span>
            </transition>
        </li>
        <li>
            <?php echo __('Update existing content'); ?>
            <transition name="fade">
                <span class="label label-info" v-if="loading === false">
                    {{ scan.to_update }} <?php echo __('files found'); ?>
                </span>
            </transition>
            <transition name="fade">
                <span class="label label-info" v-if="updated > 0">
                    {{ updated }} <?php echo __('updated'); ?>
                </span>
            </transition>
        </li>
        <li>
            <?php echo __('Remove orphan records'); ?>
            <transition name="fade">
                <span class="label label-info" v-if="loading === false">
                    {{ scan.to_remove }} <?php echo __('files to remove'); ?>
                </span>
            </transition>
            <transition name="fade">
                <span class="label label-info" v-if="removed > 0">
                    {{ removed }} <?php echo __('removed'); ?>
                </span>
            </transition>
        </li>
    </ul>
    <transition name="fade">
        <div v-show="loading">
            <p>
                <?php echo __('Please wait a few seconds while Sonerezh is scanning the filesystem.'); ?>
            </p>
            <div class="loader text-center"><i></i><i></i><i></i><i></i></div>
        </div>
    </transition>
    <transition name="fade">
        <div v-bind:class="panelClasses" v-if="loading === false && enableImport === true">
            <div class="panel-heading">
                {{ label }}
            </div>
            <div class="panel-body">
                <div class="progress" style="margin-bottom: 0;">
                    <div v-bind:style="{width: progressPercent + '%'}" v-bind:class="progressBarClasses" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="col-xs-8">
                    <span v-if="lastTrack !== null" class="help-block">
                        <?php echo __('Last track processed:'); ?> {{ lastTrack }}
                    </span>
                </div>
                <div class="col-xs-4 text-right">
                    <button v-bind:class="buttonClasses" v-on:click="startImport">{{ button }}</button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </transition>
    <transition name="fade">
        <div class="alert alert-info" v-if="loading === false && enableImport === false">
            <?php echo __('The database seems to be up to date!'); ?>
        </div>
    </transition>
    <transition>
        <ul class="list-group" v-if="logs !== null">
            <li class="list-group-item list-group-item-warning" v-for="(value, key) in logs">
                {{ key }}: {{ value }}
            </li>
        </ul>
    </transition>
</div>

<?php echo $this->start('script'); ?>
<?php echo $this->Html->script('vue.min'); ?>
<?php echo $this->Html->script('axios'); ?>
<script type="text/javascript">
    const app = new Vue({
        el: '#app',
        data () {
            return {
                button: "<?php echo __('Start Update'); ?>",
                buttonClasses: ['btn', 'btn-primary'],
                enableImport: false,
                errors: {},
                imported: 0,
                label: "<?php echo __('Standby'); ?>",
                lastTrack: null,
                loading: true,
                logs: {},
                panelClasses: ['panel', 'panel-primary'],
                progressBarClasses: ['progress-bar', 'progress-bar-info', 'progress-bar-striped', 'active'],
                progressPercent: 0,
                removed: 0,
                scan: null,
                updated: 0,
            }
        },
        mounted () {
            axios
                .get(window.location.href, {headers: {'X-Requested-With': 'XMLHttpRequest', 'X-Powered-By': 'Axios'}})
                .then(function (response) {
                    app.scan = response.data;
                    let d = response.data;
                    if (d['to_import'] + d['to_update'] + d['to_remove'] > 0) {
                        app.enableImport = true;
                    }
                })
                .catch(error => console.log(error))
                .finally(() => this.loading = false);
        },
        methods: {
            responseError: function (error) {
                if (error.response) {
                    if (error.response.status >= 500) {
                        if (error.response.data['errors']) {
                            app.label = error.response.data['errors'][error.response.data['errors'].length - 1];
                        } else {
                            app.label = "<?php echo __('An unexpected error occurred :('); ?>"
                        }
                        app.button = "<?php echo __('Error!'); ?>";
                        app.panelClasses.splice(1, 1, 'panel-danger');
                        app.buttonClasses.splice(1, 1, 'btn-danger');
                        this.progressBarClasses.splice(1, 1, 'progress-bar-danger');
                        this.progressBarClasses.splice(2, 1);
                        console.log(error.response.data);
                    } else {
                        console.log(error.response.data);
                    }
                } else if (error.request) {
                    console.log('Request was made but no response received…');
                    console.log(error.request);
                } else {
                    console.log('Error', error.message);
                }
            },
            startImport: function () {
                this.button = "<?php echo __('Running…'); ?>";
                this.label = "<?php echo __('Importing new tracks…'); ?>";
                this.panelClasses.splice(1, 1, 'panel-info');
                this.buttonClasses.splice(1, 1, 'btn-info');
                axios
                    .post(window.location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(function (response) {
                        if (response.data['errors']) {
                            console.log(Object.keys(response.data.errors));
                            app.logs = response.data.errors;
                            Object.keys(response.data.errors).forEach(function (key) {
                                app.$set(app.logs, key, response.data.errors[key]);
                            });
                        }

                        if (response.status === 201) {
                            let count = 0;
                            if (response.data.imported) {
                                count += response.data['imported'].length;
                                app.lastTrack = response.data['imported'][count - 1];
                            }
                            if (response.data.errors) {
                                count += response.data['errors'].length;
                            }
                            app.imported += count;
                            app.progressPercent = Math.round(100 * app.imported / app.scan['to_import']);
                            app.startImport();
                        }

                        if (response.status === 204) {
                            if (app.scan['to_update'] > 0) {
                                app.progressPercent = 0;
                                app.startUpdate();
                            } else if (app.scan['to_remove'] > 0) {
                                app.startCleaning();
                            } else {
                                app.importSuccess();
                            }
                        }
                    })
                    .catch(function (error) {
                        app.responseError(error);
                    })
            },
            startUpdate: function () {
                this.button = "<?php echo __('Running…'); ?>";
                this.label = "<?php echo __('Updating database…'); ?>";
                this.panelClasses.splice(1, 1, 'panel-info');
                this.buttonClasses.splice(1, 1, 'btn-info');
                axios
                    .patch(window.location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(function (response) {
                        if (response.status === 200) {
                            let count = 0;
                            if (response.data.updated) {
                                count += response.data['updated'].length;
                                app.lastTrack = response.data['updated'][count - 1];
                            }
                            if (response.data.errors) {
                                count += response.data['errors'].length;
                            }
                            app.updated += count;
                            app.progressPercent = Math.round(100 * app.updated / app.scan['to_update']);
                            app.startUpdate();
                        }

                        if (response.status === 204) {
                            if (app.scan['to_remove'] > 0) {
                                app.startCleaning();
                            } else {
                                app.importSuccess();
                            }
                        }
                    })
                    .catch(function (error) {
                        app.responseError(error);
                    })
            },
            startCleaning: function () {
                this.button = "<?php echo __('Running…'); ?>";
                this.label = "<?php echo __('Cleaning orphans from the database…'); ?>";
                this.panelClasses.splice(1, 1, 'panel-info');
                this.buttonClasses.splice(1, 1, 'btn-info');
                app.progressPercent = 100;
                axios
                    .delete(window.location.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(function (response) {
                        if (response.status === 202) {
                            app.startCleaning();
                        }

                        if (response.status === 204) {
                            app.importSuccess();
                        }
                    })
                    .catch(function (error) {
                        app.responseError(error);
                    })
            },
            importSuccess: function () {
                app.progressPercent = 100;
                this.button = "<?php echo __('Done!'); ?>";
                this.label = "<?php echo __('Import successfully done!'); ?>";
                this.panelClasses.splice(1, 1, 'panel-success');
                this.buttonClasses.splice(1, 1, 'btn-success');
                this.progressBarClasses.splice(1, 1, 'progress-bar-success');
                this.progressBarClasses.splice(2, 1);
            }
        }
    })
</script>
<?php echo $this->end(); ?>