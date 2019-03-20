<?php
/**
 * Class QRImagickTest
 *
 * @filesource   QRImagickTest.php
 * @created      04.07.2018
 * @package      chillerlan\QRCodeTest\Output
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\{QRCode, Output\QRImagick};

class QRImagickTest extends QROutputTestAbstract{

	protected $FQCN = QRImagick::class;

	public function setUp():void{

		if(!extension_loaded('imagick')){
			$this->markTestSkipped('ext-imagick not loaded');
			return;
		}

		parent::setUp();
	}

	public function testImageOutput(){
		$type = QRCode::OUTPUT_IMAGICK;

		$this->options->outputType = $type;
		$this->setOutputInterface();
		$this->outputInterface->dump($this::cachefile.$type);
		$img = $this->outputInterface->dump();

		$this->assertSame($img, file_get_contents($this::cachefile.$type));
	}

}
