<?php

namespace Wl\Skin\Application\Resource\Test\DownloadController;

use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\DownloadController::iconCreate()} method.
 *
 * @see \Wl\Skin\Application\Resource\DownloadController::iconCreate()
 */
class iconCreate extends \ATestUnit
{
  /**
   * Create context of new image.
   *
   * @param string $s_file_directory Directory where test file is.
   * @return string <tt>true</tt> context of new image.
   */
  private function _testFile(string $s_file_directory) : string
  {
    $o_download_controller = new Resource\DownloadController();
    $s_content = file_get_contents($s_file_directory);
    return $o_download_controller->iconCreate(8, 8, $s_content);
  }

  /**
   * Checks exception when method argument is not image.
   *
   * @throws \AValidateException not-image
   */
  public function testNotImage()
  {
    $o_download_controller = new Resource\DownloadController();
    return $o_download_controller->iconCreate(8, 8, 'Not an image.');
  }

  /**
   * Checks result returned by method.
   */
  public function test()
  {
    $s_file_png = lib_path('all.png','test','bin');
    $this->assertTrue(is_string($this->_testFile($s_file_png)));
  }
}

?>