<?php

namespace Wl\Skin\Application\Resource\Test\UploadController;

use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\UploadController::urlBack()} method.
 *
 * @see \Wl\Skin\Application\Resource\UploadController::urlBack()
 */
class urlBack extends \ATestUnit
{
  /**
   * Check that no errors occurred.
   */
  public function test()
  {
    $o_controller = new Resource\UploadController();
    $url = $o_controller->urlBack();
    $this->assertNotEmpty($url);
  }
}

?>