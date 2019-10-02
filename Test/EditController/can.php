<?php

namespace Wl\Skin\Application\Resource\Test\EditController;

use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditController::can()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditController::can()
 */
class can extends \ATestUnit
{
  /**
   * Check result under different initial conditions.
   */
  public function test()
  {
    $o_fixture = new \DbFixture();
    $ar_login = $o_fixture->passport_login();
    $o_fixture->save();

    passport_session($ar_login->uid);
    passport_privilege_test($ar_login->uid,'wl.skin.application.resource');
    $this->assertTrue(Resource\EditController::can());

    passport_test_privilege_clear($ar_login->uid);
    $this->assertFalse(Resource\EditController::can());

    passport_privilege_test($ar_login->uid,'resource');
    $this->assertFalse(Resource\EditController::can());
  }
}

?>