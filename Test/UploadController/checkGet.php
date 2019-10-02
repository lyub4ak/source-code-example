<?php

namespace Wl\Skin\Application\Resource\Test\UploadController;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource\UploadController;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\UploadController::checkGet()} method.
 *
 * @see \Wl\Skin\Application\Resource\UploadController::checkGet()
 */
class checkGet extends \ATestUnit
{
  /**
   * Business key.
   *
   * Primary key in {@link RsBusinessSql}.
   *
   * @var string
   */
  private $k_business;

  /**
   * User key.
   *
   * Primary key in {@link PassportLoginSql}.
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
    $o_upload_controller = new UploadController();
    $o_upload_controller->checkGet();
  }

  /**
   * Check if administrator privileges are required.
   *
   * @throws \AValidateException access
   */
  public function testAccess()
  {
    Backend::set($this->k_business);
    $o_upload_controller = new UploadController();
    $o_upload_controller->checkGet();
  }

  /**
   * Verify successful execution.
   */
  public function testSuccess()
  {
    $o_upload_controller = new UploadController();
    passport_privilege_test($this->uid,'wl');
    $o_upload_controller->checkGet();
  }
}

?>