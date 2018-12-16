<?php
    
    namespace IdnoPlugins\Video\Pages {

        use Idno\Entities\File;
        
        class Thumb extends \Idno\Common\Page {
            
            function getContent() {

                if (!empty($this->arguments)) {
                    $object = \IdnoPlugins\Video\Video::getByID($this->arguments[0]);
                } else {
                    $this->setResponse(404);
                    return;
                }

                $path = $this->createThumb($object);
                header('Content-Type: image/'. pathinfo($path, PATHINFO_EXTENSION));
                echo file_get_contents($path);
            }

            /** requires ffmpeg installed on server
             * falls back to empty gif image
             */
            function createThumb($video) {
                $path = sys_get_temp_dir() . '/' . $video['_id'] . '.jpg';
                if (!file_exists($path)) {
                    require_once dirname(__DIR__) . '/vendor/autoload.php';
                    $fileId = $video->attachments[0]['_id'];
                    $file = File::getByID($fileId);
                    $movie = $file->internal_filename;
                    $video = \FFMpeg\FFMpeg::create()->open($movie);
                    $frame = $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1));
                    $frame->save($path);
                }
                return $path;
            }

            function exception($e)
            {
                if($e->getMessage() == 'Unable to load FFMpeg') {
                    header('Content-Type: image/gif');
                    echo $this->emptyImage();
                }
            }

            function emptyImage() {
                return base64_decode('data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
            }
        }


    }