<?php
/**
 * Class QRImage
 *
 * @filesource   QRImage.php
 * @created      05.12.2015
 * @package      chillerlan\QRCode\Output
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\QRCode;

/**
 * Converts the matrix into GD images, raw or base64 output
 * requires ext-gd
 * @link http://php.net/manual/book.image.php
 */
class QRImage extends QROutputAbstract{

	protected const TRANSPARENCY_TYPES = [
		QRCode::OUTPUT_IMAGE_PNG,
		QRCode::OUTPUT_IMAGE_GIF,
	];

	/**
	 * @var string
	 */
	protected $defaultMode  = QRCode::OUTPUT_IMAGE_PNG;

	/**
	 * @see imagecreatetruecolor()
	 * @var resource
	 */
	protected $image;

	/**
	 * @see imagecolorallocate()
	 * @var int
	 */
	protected $background;

	/**
	 * @return void
	 */
	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!\is_array($v) || \count($v) < 3){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? [0, 0, 0]
					: [255, 255, 255];
			}
			else{
				$this->moduleValues[$M_TYPE] = \array_values($v);
			}

		}

	}

	/**
	 * @param string|null $file
	 *
	 * @return string
	 */
	public function dump(string $file = null):string{
		$this->image      = \imagecreatetruecolor($this->length, $this->length);
		$this->background = \imagecolorallocate($this->image, ...$this->options->imageTransparencyBG);

		if((bool)$this->options->imageTransparent && \in_array($this->options->outputType, $this::TRANSPARENCY_TYPES, true)){
			\imagecolortransparent($this->image, $this->background);
		}

		\imagefilledrectangle($this->image, 0, 0, $this->length, $this->length, $this->background);

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$this->setPixel($x, $y, $this->moduleValues[$M_TYPE]);
			}
		}

		$imageData = $this->dumpImage($file);

		if((bool)$this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.\base64_encode($imageData);
		}

		return $imageData;
	}

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param array $rgb
	 *
	 * @return void
	 */
	protected function setPixel(int $x, int $y, array $rgb):void{
		\imagefilledrectangle(
			$this->image,
			$x * $this->scale,
			$y * $this->scale,
			($x + 1) * $this->scale,
			($y + 1) * $this->scale,
			\imagecolorallocate($this->image, ...$rgb)
		);
	}

	/**
	 * @param string|null $file
	 *
	 * @return string

	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	protected function dumpImage(string $file = null):string{
		$file = $file ?? $this->options->cachefile;

		\ob_start();

		try{
			\call_user_func([$this, $this->outputMode ?? $this->defaultMode]);
		}
		// not going to cover edge cases
		// @codeCoverageIgnoreStart
		catch(\Exception $e){
			throw new QRCodeOutputException($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		$imageData = \ob_get_contents();
		\imagedestroy($this->image);

		\ob_end_clean();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		return $imageData;
	}

	/**
	 * @return void
	 */
	protected function png():void{
		\imagepng(
			$this->image,
			null,
			\in_array($this->options->pngCompression, \range(-1, 9), true)
				? $this->options->pngCompression
				: -1
		);
	}

	/**
	 * Jiff - like... JitHub!
	 * @return void
	 */
	protected function gif():void{
		\imagegif($this->image);
	}

	/**
	 * @return void
	 */
	protected function jpg():void{
		\imagejpeg(
			$this->image,
			null,
			\in_array($this->options->jpegQuality, \range(0, 100), true)
				? $this->options->jpegQuality
				: 85
		);
	}

}
