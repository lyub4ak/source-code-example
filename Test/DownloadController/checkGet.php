<?php

namespace Wl\Skin\Application\Resource\Test\DownloadController;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource\DownloadController;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\DownloadController::checkGet()} method.
 *
 * @see \Wl\Skin\Application\Resource\DownloadController::checkGet()
 */
class checkGet extends \ATestUnit
{
  /**
   * Test business ID.
   *
   * @var string
   */
  private $k_business;

  /**
   * Test user ID.
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
    $o_download_controller = new DownloadController();
    $o_download_controller->checkGet();
  }

  /**
   * Check if administrator privileges are required.
   *
   * @throws \AValidateException access
   */
  public function testAccess()
  {
    Backend::set($this->k_business);
    $o_download_controller = new DownloadController();
    $o_download_controller->checkGet();
  }

  /**
   * Verify successful execution.
   */
  public function testSuccess()
  {
    $o_download_controller = new DownloadController();
    passport_privilege_test($this->uid,'wl');
    $o_download_controller->checkGet();
  }
}

?>