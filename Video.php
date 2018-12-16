<?php

    namespace IdnoPlugins\Video {

        use Idno\Entities\File;

        class Video extends \Idno\Common\Entity
        {

            // http://php.net/manual/en/features.file-upload.errors.php
	        private static $FILE_UPLOAD_ERROR_CODES = array(
		        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
		        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
		        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
		        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
		        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
	        );

            function getTitle()
            {
                if (empty($this->title)) {
                    return 'Untitled';
                } else {
                    return $this->title;
                }
            }

            function getDescription()
            {
                return $this->body;
            }

            /**
             * Video objects have type 'video'
             * @return 'video'
             */
            function getActivityStreamsObjectType()
            {
                return 'video';
            }

            /**
             * Extend json serialisable to include some extra data
             */
            public function jsonSerialize()
            {
                $object = parent::jsonSerialize();

                // Add some thumbs
                $object['thumbnails'] = array();
                $sizes                = \Idno\Core\Idno::site()->events()->dispatch('video/thumbnail/getsizes', new \Idno\Core\Event(array('sizes' => array('large' => 800, 'medium' => 400, 'small' => 200))));
                $eventdata = $sizes->data();
                foreach ($eventdata['sizes'] as $label => $size) {
                    $varname                      = "thumbnail_{$label}";
                    $object['thumbnails'][$label] = preg_replace('/^(https?:\/\/\/)/', \Idno\Core\Idno::site()->config()->url, $this->$varname);
                }

                return $object;
            }


            /**
             * Saves changes to this object based on user input
             * @return bool
             */
            function saveDataFromInput()
            {

                if (empty($this->_id)) {
                    $new = true;
                } else {
                    $new = false;
                }

                if ($new) {
                    if (!\Idno\Core\Idno::site()->triggerEvent("file/upload",[],true)) {
                        return false;
                    }
                }

                $this->title = \Idno\Core\Idno::site()->currentPage()->getInput('title');
                $this->body  = \Idno\Core\Idno::site()->currentPage()->getInput('body');
                $this->tags  = \Idno\Core\Idno::site()->currentPage()->getInput('tags');
                $access = \Idno\Core\Idno::site()->currentPage()->getInput('access');
                $this->setAccess($access);

                if ($time = \Idno\Core\Idno::site()->currentPage()->getInput('created')) {
                    if ($time = strtotime($time)) {
                        $this->created = $time;
                    }
                }

                // Get video
                if ($new) {
                    if (!empty($_FILES['video']['tmp_name'])) {
                        if (static::isVideo($_FILES['video']['tmp_name'])) {
                            if ($video = \Idno\Entities\File::createFromFile($_FILES['video']['tmp_name'], $_FILES['video']['name'], $_FILES['video']['type'], true, true)) {
                                $this->attachFile($video);

                                /*
                                this wont work as its a VIDEO 

                                // Now get some smaller thumbnails, with the option to override sizes
                                $sizes = \Idno\Core\Idno::site()->events()->dispatch('video/thumbnail/getsizes', new \Idno\Core\Event(array('sizes' => array('large' => 800, 'medium' => 400, 'small' => 200))));
                                $eventdata = $sizes->data();
                                var_dump($eventdata);die;
                                foreach ($eventdata['sizes'] as $label => $size) {

                                    $filename = $_FILES['video']['name'];

                                    if ($thumbnail = \Idno\Entities\File::createThumbnailFromFile($_FILES['video']['tmp_name'], "{$filename}_{$label}", $size, false)) {
                                        $varname        = "thumbnail_{$label}";
                                        $this->$varname = \Idno\Core\Idno::site()->config()->url . 'file/' . $thumbnail;

                                        $varname        = "thumbnail_{$label}_id";
                                        $this->$varname = substr($thumbnail, 0, strpos($thumbnail, '/'));
                                    }
                                }

                                */

                            } else {
                                \Idno\Core\Idno::site()->session()->addErrorMessage('Video wasn\'t attached.');
                                return false;
                            }
                        } else {
                            \Idno\Core\Idno::site()->session()->addErrorMessage('This doesn\'t seem to be a video ..');
                            return false;
                        }
                    } else {
	                    // http://php.net/manual/en/features.file-upload.errors.php
	                    $errcode = $_FILES['video']['error'];
	                    if (!empty($errcode) && !empty(self::$FILE_UPLOAD_ERROR_CODES[intval($errcode)])) {
		                    $errmsg = self::$FILE_UPLOAD_ERROR_CODES[intval($errcode)];
	                    } else {
		                    $errmsg = 'We couldn\'t access your video for an unknown reason. Please try again.';
	                    }
	                    \Idno\Core\Idno::site()->session()->addErrorMessage($errmsg);
                        return false;
                    }
                }

                if ($this->publish($new)) {

                    if ($this->getAccess() == 'PUBLIC') {
                        \Idno\Core\Webmention::pingMentions($this->getURL(), \Idno\Core\Idno::site()->template()->parseURLs($this->getTitle() . ' ' . $this->getDescription()));
                    }

                    return true;
                } else {
                    return false;
                }

            }

            /**
             * Determines whether a file is an image or not.
             * @param string $file_path The path to a file
             * @return bool
             */
            public static function isVideo($file_path)
            {
                $mimetype = shell_exec('file --brief --mime ' . escapeshellarg($file_path));
                if ($mimetype == '') {
                    return false;
                }
                list($media, $subtype) = explode('/', $mimetype);
                return $media === 'video';
            }

        }

    }
