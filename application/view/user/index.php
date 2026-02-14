<div class="main-content">
                

        <!-- /section:basics/content.breadcrumbs -->
       <div class="page-content">
                        <div class="row">
                            <div class="col-xs-12">
                                <!-- PAGE CONTENT BEGINS -->

        <h2>Profile</h2>

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <div>Uusername: <?= $this->user_name; ?></div>
        <div>Your avatar image:<br>
            <?php if (Config::get('USE_GRAVATAR')) { ?>
                <img src='<?= $this->user_gravatar_image_url; ?>' />
            <?php } else { ?>
                <img src='<?= Config::get('URL') . 'avatars/' . Session::get('uid') . '_medium.jpg'; ?>' />
            <?php } ?>
        </div>


</div>
</div>
</div>
</div>
