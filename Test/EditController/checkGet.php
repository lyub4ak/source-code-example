<?php

namespace Wl\Skin\Application\Resource\Test\EditController;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource\EditController;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditController::checkGet()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditController::checkGet()
 */
class checkGet extends \ATestUnit
{
  /**
   * Current business ID.
   *
   * @var string
   */
  private $k_business;

  /**
   * Current user ID.
   *
   * @var string
   */
  private $uid;

  /**
   * @inheritDoc
   */
  public function setUpBeforeClass()
  {
    $o_fixture = new \DbFixture();
    $ar_business = $o_fixture->rs_business();
    $ar_login = $o_fixture->passport_login();
    $o_fixture->save();

    $this->k_business = $ar_business->k_business;
    $this->uid = $ar_login->uid;
    passport_session($this->uid);
  }

  /**
   * Check if an exception is thrown when user is not in business backend.
   *
   * @throws \AValidateException backend
   */
  public function testBackend()
  {
    $o_edit_controller = new EditController();
    $o_edit_controller->checkGet();
  }

  /**
   * Check if administrator privileges are required.
   *
   * @throws \AValidateException access
   */
  public function testAccess()
  {
    Backend::set($this->k_business);
    $o_edit_controller = new EditController();
    $o_edit_controller->checkGet();
  }

  /**
   * Verify successful execution.
   */
  public function testSuccess()
  {
    $o_edit_controller = new EditController();
    passport_privilege_test($this->uid,'wl');
    $o_edit_controller->checkGet();
  }
}

?>