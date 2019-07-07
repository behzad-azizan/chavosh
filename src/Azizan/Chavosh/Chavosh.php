<?php


namespace Azizan\Chavosh;


use Azizan\Chavosh\Exceptions\FileNotFoundException;
use Azizan\Chavosh\Interfaces\ChavoshInterface;
use Azizan\Chavosh\Interfaces\PhraseBuilderInterface;

class Chavosh implements ChavoshInterface
{
    const SHAPE_CIRCLE = 'circle';
    const SHAPE_DIAMOND = 'diamond';
    const SHAPE_SQUARE = 'square';
    const SHAPE_STAR = 'star';

    /**
     * @var string
     */
    protected $phrase;

    /**
     * @var int
     */
    protected $width = 200;

    /**
     * @var int
     */
    protected $height = 30;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var string
     */
    protected $textColor;

    /**
     * @var string
     */
    protected $backgroundColor;

    protected $background;

    /**
     * @var string|array
     */
    protected $shapesColor;

    /**
     * @var string
     */
    protected $fontPath;

    /**
     * @var string
     */
    protected $shapesType = self::SHAPE_CIRCLE;

    /**
     * @var int
     */
    protected $shapesCount = 8;

    /**
     * @var int
     */
    protected $rotateAngle = 0;

    /**
     * @var resource
     */
    protected $contents = null;

    /**
     * @var PhraseBuilderInterface
     */
    protected $phraseBuilder;

    /**
     * Chavosh constructor.
     * @param null|string $specialPhrase
     * @param PhraseBuilderInterface|null $phraseBuilder
     * @throws FileNotFoundException
     */
    public function __construct($specialPhrase = null, PhraseBuilderInterface $phraseBuilder = null)
    {
        $this->phraseBuilder = $phraseBuilder = is_null($phraseBuilder) ? new PhraseBuilder() : $phraseBuilder;
        $this->phrase = $specialPhrase ? $specialPhrase : $phraseBuilder->getPhrase();
        $this->length = mb_strlen($this->phrase, 'UTF-8');
        $this->setFont(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'IRANSansWeb_Black.ttf');
        $this->setWidth(500);
        $this->setHeight(150);
        $this->setBackgroundColor('#fff');
        $this->setShapesColor([
            ['background' => '#ea4335', 'border' => '#f5897f'],
            ['background' => '#4285f4', 'border' => '#80aefb'],
            ['background' => '#fbbc05', 'border' => '#f5897f'],
            ['background' => '#34a853', 'border' => '#80b89a'],
        ]);
    }

    /**
     * Setting the phrase
     * @param string $phrase
     * @return $this
     */
    public function setPhrase($phrase)
    {
        $this->phrase = strval($phrase);
        return $this;
    }

    /**
     * Gets the captcha phrase
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * Sets the text color
     * @param string $color the Hex Color Code
     * @return Chavosh
     */
    public function setTextColor($color)
    {
        $this->textColor = $color;
        return $this;
    }

    /**
     * Sets the background color
     * @param string $color the Hex Color Code
     * @return $this
     */
    public function setBackgroundColor($color)
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setShapesCount($count)
    {
        $this->shapesCount = $count;
        return $this;
    }

    /**
     * @param array $colors
     * @return Chavosh
     */
    public function setShapesColor($colors)
    {
        $this->shapesColor = $colors;
        return $this;
    }

    /**
     * Sets the captcha image width
     * @param int $width
     * @return Chavosh
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Sets the captcha image height
     * @param int $height
     * @return Chavosh
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param string $ttfPath
     * @return $this
     * @throws FileNotFoundException
     */
    public function setFont($ttfPath)
    {
        if (! file_exists($ttfPath))
            throw new FileNotFoundException('The ttf file path is invalid');

        $this->fontPath = $ttfPath;

        return $this;
    }

    /**
     * @param string $shape
     * @return $this
     */
    public function setShapeType($shape)
    {
        $this->shapesType = $shape;
        return $this;
    }

    /**
     * @param $degree
     * @return $this
     */
    public function rotate($degree)
    {
        $this->rotateAngle = $degree;
        return $this;
    }

    /**
     * Instantiation
     * @param null $phrase
     * @param PhraseBuilderInterface|null $phraseBuilder
     * @return Chavosh
     * @throws FileNotFoundException
     */
    public static function create($phrase = null, PhraseBuilderInterface $phraseBuilder = null)
    {
        return new self($phrase, $phraseBuilder);
    }

    /**
     * Writes the phrase on the image
     * TODO check this
     */
    protected function writePhrase($image, $phrase, $font, $width, $height)
    {
        $length = mb_strlen($phrase);
        if ($length === 0) {
            return \imagecolorallocate($image, 0, 0, 0);
        }

        // Gets the text size and start position
        $size = $width / $length - rand(0, 3) - 1;
        $box = \imagettfbbox($size, 0, $font, $phrase);
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2 + $size;

        if (!$this->textColor) {
            $textColor = array(rand(0, 150), rand(0, 150), rand(0, 150));
        } else {
            $textColor = $this->textColor;
        }
        $col = \imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

        // Write the letters one by one, with random angle
        for ($i=0; $i<$length; $i++) {
            $symbol = mb_substr($phrase, $i, 1);
            $box = \imagettfbbox($size, 0, $font, $symbol);
            $w = $box[2] - $box[0];
//            $angle = rand(-$this->maxAngle, $this->maxAngle);
//            $offset = rand(-$this->maxOffset, $this->maxOffset);
            \imagettftext($image, $size, 0, $x, $y + 0, $col, $font, $symbol);
            $x += $w;
        }

        return $col;
    }

    public function build($width = null, $height = null)
    {
        if ($width)
            $this->setWidth($width);
        if ($height)
            $this->setHeight($height);

        $image   = imagecreatetruecolor($this->width, $this->height);

        if ($this->backgroundColor == null)
            $bg = imagecolorallocate($image, 255, 255, 255);
        else {
//            $color = $this->hexToRgb($this->backgroundColor);
            $bg = imagecolorallocate($image, ...$this->hexToRgb($this->backgroundColor));
        }
        $this->background = $bg;
        imagefill($image, 0, 0, $bg);
        $this->drawShapes($image);
        $color = $this->writePhrase($image, $this->phrase, $this->fontPath, $this->width, $this->height);
        $this->contents = $image;

        return $this;
    }

    protected function drawShapes(&$image)
    {
        $shapeLocationBorder = floor($this->width / $this->shapesCount);

        for ($i = 1; $i <= $this->shapesCount; $i++) {
            $color = $this->shapesColor[array_rand($this->shapesColor)];
            // the border color has been set
            if (is_array($color)) {
                $shapeColor = $color['background'];
                $bordercolor = $color['border'];
            }
            $ellipseColor = imagecolorallocate($image, ...$this->hexToRgb($shapeColor));
            imagecolortransparent($image, $ellipseColor);
            $radius = rand($this->height/5, $this->height);

            $cxStart = ($i - 1) * $shapeLocationBorder;
            $cxFinish = $i * $shapeLocationBorder;
            $cx = rand($cxStart, $cxFinish);
            $cy = rand(-10, $this->height);
            if (isset($bordercolor)) {
                $bordercolor = imagecolorallocate($image, ...$this->hexToRgb($bordercolor));
                imagefilledellipse($image,$cx,$cy,$radius + 2,$radius + 2,$bordercolor);
            }

            imagefilledellipse($image, $cx, $cy, $radius, $radius, $ellipseColor);
        }

    }

    /**
     * Convert hex color code to RGB
     * @param $hex
     * @param bool $alpha
     * @return array
     */
    protected function hexToRgb($hex) {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb[] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb[] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb[] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return $rgb;
    }

    /**
     * Save the captcha image to a jpeg file
     * @param string $filePath
     * @param int $quality
     */
    public function saveAs($filePath, $quality = 100)
    {
        imagejpeg($this->contents, $filePath, $quality);
    }

    /**
     * Gets the image GD object
     */
    public function getGd()
    {
        return $this->contents;
    }

    /**
     * Gets the image
     * @param int $quality
     * @return false|string
     */
    public function get($quality = 100)
    {
        ob_start();
        $this->output($quality);

        return ob_get_clean();
    }

    /**
     * Gets the image inline base64
     * @param int $quality
     * @return string
     */
    public function inline($quality = 100)
    {
        return 'data:image/jpeg;base64,' . base64_encode($this->get($quality));
    }

    /**
     * Outputs the image
     * @param int $quality
     */
    public function output($quality = 100)
    {
        imagejpeg($this->contents, null, $quality);
    }
}