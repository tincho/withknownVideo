<?php

    namespace IdnoPlugins\Video\Pages {

        class Edit extends \Idno\Common\Page {

            function getContent() {

                $this->createGatekeeper();    // This functionality is for logged-in users only

                // Are we loading an entity?
                if (!empty($this->arguments)) {
                    $object = \IdnoPlugins\Video\Video::getByID($this->arguments[0]);
                } else {
                    $object = new \IdnoPlugins\Video\Video();
                }

                if ($owner = $object->getOwner()) {
                    $this->setOwner($owner);
                }

                $t = \Idno\Core\Idno::site()->template();
                $edit_body = $t->__(array(
                    'object' => $object
                ))->draw('entity/Video/edit');

                $body = $t->__(['body' => $edit_body])->draw('entity/editwrapper');

                if (empty($object)) {
                    $title = 'Post a video';
                } else {
                    $title = 'Edit video description';
                }

                if (!empty($this->xhr)) {
                    echo $body;
                } else {
                    $t->__(array('body' => $body, 'title' => $title))->drawPage();
                }
            }

            function postContent() {
                $this->createGatekeeper();

                $new = false;
                if (!empty($this->arguments)) {
                    $object = \IdnoPlugins\Video\Video::getByID($this->arguments[0]);
                }
                if (empty($object)) {
                    $object = new \IdnoPlugins\Video\Video();
                }

                if ($object->saveDataFromInput()) {
                    $forward = $this->getInput('forward-to', $object->getDisplayURL());
                    $this->forward($forward);
                }

            }

        }

    }