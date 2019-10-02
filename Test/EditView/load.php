<?php

namespace Wl\Skin\Application\Resource\Test\EditView;

use Wl\Backend\Backend;
use Wl\Skin\Application\Resource\EditController;
use Wl\Skin\Application\Resource\EditView;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditView::load()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditView::load()
 */
class load extends \ATestUnit
{
  /**
   * Checks that the method returns an array with a specific structure and data.
   */
  public function test()
  {
    $o_fixture = new \DbFixture();

    $ar_business_a = $o_fixture->rs_business();

    $ar_business_b = $o_fixture->rs_business();

    $ar_location = $o_fixture->rs_location();
    $ar_location->k_business = $ar_business_a;
    $ar_location->s_email = 'test-email@gmail.com';
    $ar_location->s_phone = '111-111-1111';

    $ar_skin_application_resource = $o_fixture->wl_skin_application_resource();
    $ar_skin_application_resource->k_business = $ar_business_b->k_business;
    $ar_skin_application_resource->text_description = 'Description';
    $ar_skin_application_resource->text_email = 'test@milo.ru';
    $ar_skin_application_resource->text_phone = '111-111-1111';
    $ar_skin_application_resource->text_website = 'site.com';

    $o_fixture->save();

    //Checks the method without data for current business in the database.
    Backend::set($ar_business_a->k_business);
    $o_edit_controller = new EditController();
    $o_edit_controller->k_business = $ar_business_a->k_business;
    $o_edit_controller->text_name = 'text_name';
    $o_edit_controller->text_website = 'test.web.com';
    $o_edit_view = new EditView();
    $a_load = $o_edit_view->load($o_edit_controller);

    $this->assertArrayCount(18, $a_load);
    $this->assertArrayHasKey('o_controller', $a_load);
    $this->assertTrue( $a_load['o_controller'] instanceof EditController);
    $this->assertStringEquals($ar_location->s_email, $a_load['html_email']);
    $this->assertStringEquals($o_edit_controller->text_name, $a_load['html_name']);
    $this->assertStringEquals($ar_location->s_phone, $a_load['html_phone']);
    $this->assertStringEquals($o_edit_controller->text_website, $a_load['html_website']);

    //Checks the method in read mode from the database.
    Backend::set($ar_business_b->k_business);
    $o_edit_controller = new EditController();
    $o_edit_controller->k_business = $ar_business_b->k_business;
    $o_edit_view = new EditView();

    $html_template = $o_edit_view->render($o_edit_controller);
    $this->assertXss($html_template);
    $this->assertStringContains(
      htmlspecialchars($ar_skin_application_resource->text_name),
      $html_template
    );

    $this->assertStringContains(
      htmlspecialchars($ar_skin_application_resource->text_description),
      $html_template
    );

    $this->assertStringContains(
      htmlspecialchars($ar_skin_application_resource->text_email),
      $html_template
    );

    $this->assertStringContains(
      htmlspecialchars($ar_skin_application_resource->text_phone),
      $html_template
    );

    $this->assertStringContains(
      htmlspecialchars($ar_skin_application_resource->text_website),
      $html_template
    );
  }
}

?>