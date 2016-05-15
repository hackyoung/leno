<?php
namespace Test\Model;

class Image extends \Leno\ORM\Mapper
{
    const TYPE_JPG = 'image/jpg';

    const TYPE_JPEG = 'image/jpeg';

    const TYPE_PNG = 'image/png';

    const TYPE_GIF = 'image/gif';

    public static $attributes = [
        'image_id' => ['type' => 'uuid'],
        'app_id' => ['type' => 'uuid'],
        'name' => ['type' => 'string', 'extra' => ['max_length' => 128]],
        'md5' => ['type' => 'string', 'extra' => ['max_length' => 128]],
        'path_file' => ['type' => 'string', 'extra' => ['max_length' => 128]],
        'width' => ['type' => 'int', 'extra' => ['min' => 0]],
        'height' => ['type' => 'int', 'extra' => ['min' => 0]],
        'size' => ['type' => 'int', 'extra' => ['min' => 0]],
        'type' => ['type' => 'enum', 'extra' => [
            'enum_list' => [self::TYPE_JPG, self::TYPE_JPEG, self::TYPE_PNG, self::TYPE_GIF]
        ]],
        'created' => ['type' => 'datetime'],
        'updated' => ['type' => 'datetime', 'required' => false],
        'deleted' => ['type' => 'datetime', 'required' => false],
    ];

    public static $primary = 'image_id';

    public static $table = 'image';

    private $base_dir = ROOT . '/image';

    public function addNew($appid, $file)
    {
        $md5 = md5_file($file['tmp_name']);
        $here = self::selector()->byEqMd5($md5)
            ->findOne();
        if($here instanceof self) {
            return $here->getImageId();
        }
        $dir = $this->base_dir . '/' . date('Y/m/d');
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->setPathFile($dir . '/' . $md5);
        if(!move_uploaded_file($file['tmp_name'], $this->getPathFile())) {
            throw new \Leno\Exception('Move File Error');
        }
        list($width, $height, $type, $attr) = array_values(getimagesize($this->getPathFile()));
        var_dump($width);
        var_dump($height);
        var_dump($file['type']);
        var_dump($dir .'/'. $md5);
        /*
        $this->setWidth($width);
        $this->setAppId($appid);
        $this->setHeight($height);
        $this->setType($file['type']);
        $this->setSize($file['size']);
        $this->setName($file['name']);
        $this->setCreated(new \Datetime);
        try {
        $this->save();
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
        echo self::creator()->getSql();
         */
        return $this->getImageId();
    }

    public function resize($w = null, $h = null)
    {
        if($w !== null && $h !== null) {
            return $this->resizeByWidthHeight($w, $h);
        } elseif($w !== null && $h === null) {
            return $this->resizeByWidth($w);
        } elseif($w === null && $h !== null) {
            return $this->resizeByHeight($h);
        } else {
            return file_get_contents($this->getPathFile());
        }
    }

    private function resizeByWidth($w)
    {
        $h = $w/$this->getWidth()*$this->getHeight();
        return $this->resizeByWidthHeight($w, $h);
    }

    private function resizeByHeight($h)
    {
        $h = $w/$this->getWidth()*$this->getHeight();
        return $this->resizeByWidthHeight($w, $h);
    }

    private function resizeByWidthHeight($w, $h)
    {
        $file = $this->getPathFile();
        $image_type = $this->getType();
        switch ($image_type)
        {
            case self::TYPE_GIF: 
                $src = imagecreatefromgif($file);
                break;
            case self::TYPE_JPEG:
            case self::TYPE_JPG:
                $src = imagecreatefromjpeg($file);
                break;
            case self::TYPE_PNG:
                $src = imagecreatefrompng($file);
                break;
            default:
                throw new \Leno\Exception ('Unsupported Image Type');
        }

        $tmp = imagecreatetruecolor($w, $h);

        /* Check if this image is PNG or GIF, then set if Transparent*/
        if($image_type == self::TYPE_GIF || $image_type == self::TYPE_PNG) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp,true);
            $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
            imagefilledrectangle($tmp, 0, 0, $w, $h, $transparent);
        }
        imagecopyresampled($tmp, $src, 0,0,0,0,$w, $h, $this->getWidth(), $this->getHeight());
        ob_start();
        switch ($image_type) {
            case self::TYPE_GIF:
                imagegif($tmp);
                break;
            case self::TYPE_JPG:
            case self::TYPE_JPEG:
                imagejpeg($tmp, NULL, 100);
                break; // best quality
            case self::TYPE_PNG:
                imagepng($tmp, NULL, 0);
                break; // no compression
            default:
                echo '';
                break;
        }
        $final_image = ob_get_contents();
        ob_end_clean();
        return $final_image;
    }
}
