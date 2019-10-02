<?php

namespace Wl\Skin\Application\Resource\Test\EditController;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditController::resourceStructure()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditController::resourceStructure()
 */
class resourceStructure extends \ATestUnit
{
  /**
   * Checks that the method returns an array with a specific structure.
   */
  public function test()
  {
    $o_fixture = new \DbFixture();
    $ar_business = $o_fixture->rs_business();
    $o_fixture->save();
    Backend::set($ar_business->k_business);

    $this->assertArrayHasKey('a_group', Resource\EditController::resourceStructure()[0]);
    $this->assertArrayHasKey('a_image', Resource\EditController::resourceStructure()[0]['a_group'][0]);
  }
}

?>