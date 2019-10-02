<?php

namespace Wl\Skin\Application\Resource\Test\DownloadController;

use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\DownloadController::urlBack()} method.
 *
 * @see \Wl\Skin\Application\Resource\DownloadController::urlBack()
 */
class urlBack extends \ATestUnit
{
  /**
   * Check that no errors occurred.
   */
  public function test()
  {
    $o_controller = new Resource\DownloadController();
    $url = $o_controller->urlBack();
    $this->assertNotEmpty($url);
  }
}
?>