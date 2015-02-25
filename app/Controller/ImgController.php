<?php

App::uses('AppController', 'Controller');

/**
 * Class ImgController
 * Manage on-the-fly image resizing. All image calls are processed by this controller.
 *
 * @see ImageComponent
 * @property Img $Img
 */
class ImgController extends AppController {

    public $components = array('Image');

    /**
     * This function explodes the passed path in param to retrieve the dimensions of the resized image.
     * It uses ImageComponent to resize the image.
     *
     * @param string $img Original image path.
     * @return CakeResponse Resized image path.
     */
    public function index($img) {
        preg_match("/.*(_([0-9]+)x([0-9]+))\.[a-z0-9]+$/i", $img, $format);
        $dimensions = array($format[2], $format[3]);
        $path = IMAGES.str_replace($format[1], '', $img);
        $resized = RESIZED_DIR.pathinfo($img, PATHINFO_BASENAME);

        if (!file_exists($path)) {
            throw new NotFoundException();
        }

        if (!file_exists($resized)) {
            if (!file_exists(RESIZED_DIR)) {
                App::uses('Folder', 'Utility');
                new Folder(RESIZED_DIR, true, 0777);
            }
            $this->Image->resize($path, $resized, $dimensions[0], $dimensions[1]);
        }

        $this->response->file($resized);
        return $this->response;
    }
}