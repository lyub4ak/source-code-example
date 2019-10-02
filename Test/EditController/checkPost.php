<?php

namespace Wl\Skin\Application\Resource\Test\EditController;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditController::checkPost()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditController::checkPost()
 */
class checkPost extends \ATestUnit
{
  /**
   * @inheritDoc
   */
  public function setUpBeforeClass()
  {
    $o_fixture = new \DbFixture();
    $ar_business = $o_fixture->rs_business();
    $o_fixture->save();
    Backend::set($ar_business->k_business);
  }

  /**
   * Check method with error.
   */
  public function testError()
  {
    $o_edit_controller = new Resource\EditController();
    $o_edit_controller->id_category = 333;
    $o_edit_controller->text_website = 'htp';
    $o_edit_controller->checkPost();
    $a_error = $o_edit_controller->errorGet();
    $this->assertArrayContainsValue('category-nx', $a_error[0]);
    $this->assertArrayContainsValue('url-invalid', $a_error[1]);
  }

  /**
   * Check method without error.
   */
  public function testOk()
  {
    $o_edit_controller = new Resource\EditController();
    $o_edit_controller->id_category = 3;
    $o_edit_controller->text_website = 'http://demo.wellnessliving.com/rs/microsite.html?k_location=1&sid_microsite_page=review';
    $o_edit_controller->checkPost();
    $a_error = $o_edit_controller->errorGet();
    $this->assertEmpty($a_error);
  }
}

?>