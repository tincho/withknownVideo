<?= $this->draw('entity/edit/header'); ?>
    <form action="<?= $vars['object']->getURL() ?>" method="post" enctype="multipart/form-data">

        <div class="row">

            <div class="col-md-8 col-md-offset-2 edit-pane">

                <h4>
                    <?php

                        if (empty($vars['object']->_id)) {
                            ?>New Video<?php
                        } else {
                            ?>Edit Video<?php
                        }

                    ?>
                </h4>

                <?php

                    if (empty($vars['object']->_id)) {

                        ?>
                        <div id="video-preview"></div>
                        <p>
                                <span class="btn btn-primary btn-file">
                                        <i class="fa fa-video-camera"></i> <span
                                        id="video-filename">Select a video</span> <input type="file" name="video"
                                                                                         id="video"
                                                                                         class="col-md-9 form-control"
                                                                                         accept="video/*;"
                                                                                         onchange="videoPreview(this)"/>

                                    </span>
                        </p>

                    <?php

                    }

                ?>

                <div id="video-details" style="<?php

                    /*if (empty($vars['object']->_id)) {
                        echo 'display:none';
                    }*/

                    ?>">

                    <div class="content-form">
                        <label for="title">
                            Title</label>
                        <input type="text" name="title" id="title"
                               value="<?= htmlspecialchars($vars['object']->title) ?>" class="form-control"
                               placeholder="Give it a title"/>
                    </div>

                    <?= $this->__([
                        'name' => 'body',
                        'value' => $vars['object']->body,
                        'wordcount' => false,
                        'class' => 'wysiwyg-short',
                        'height' => 100,
                        'placeholder' => 'Describe your video',
                        'label' => 'Description'
                    ])->draw('forms/input/richtext')?>

                    <?= $this->draw('entity/tags/input'); ?>

                </div>
                <div id="video-details-toggle" style="<?php
                    //if (!empty($vars['object']->_id)) {
                        echo 'display:none';
                    //}
                ?>">
                    <p>
                        <small><a href="#" onclick="$('#video-details').show(); $('#video-details-toggle').hide(); return false;">+ Add details</a></small>
                    </p>
                </div>

                <?php echo $this->drawSyndication('image', $vars['object']->getPosseLinks()); ?>
                <?php if (empty($vars['object']->_id)) { ?><input type="hidden" name="forward-to"
                                                                  value="<?= \Idno\Core\Idno::site()->config()->getDisplayURL() . 'content/all/'; ?>" /><?php } ?>
                <?= $this->draw('content/access'); ?>
                <p class="button-bar ">
                    <?= \Idno\Core\Idno::site()->actions()->signForm('/video/edit') ?>
                    <input type="button" class="btn btn-cancel" value="Cancel" onclick="hideContentCreateForm();"/>
                    <input type="submit" class="btn btn-primary" value="Publish"/>
                </p>
            </div>

        </div>
    </form>
    <script>
        //if (typeof videoPreview !== function) {
        function videoPreview(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#video-preview').html('<video src="" id="videopreview" style="display:none; width: 400px" controls="controls"/>');
                    $('#video-filename').html('Choose different video');
                    $('#videopreview').attr('src', e.target.result);
                    $('#videopreview').show();
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
        //}
    </script>
<?= $this->draw('entity/edit/footer'); ?>