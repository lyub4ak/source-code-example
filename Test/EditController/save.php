<?php

namespace Wl\Skin\Application\Resource\Test\EditController;

use Core\All\File;
use Core\Drive\Drive;
use Wl\Backend\Backend;
use Wl\Skin\Application\Resource\EditController;
use Wl\Skin\Application\Resource\Image;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\EditController::save()} method.
 *
 * @see \Wl\Skin\Application\Resource\EditController::save()
 */
class save extends \ATestUnit
{
  /**
   * Checks data added to the database
   */
  public function test()
  {
    $o_fixture = new \DbFixture();
    $ar_business = $o_fixture->rs_business();
    $o_fixture->save();
    Backend::set($ar_business->k_business);

    //Prepares image for save in controller.
    $a_resource = EditController::resourceStructure();
    $s_link = Image::applicationLink($ar_business->k_business, $a_resource[0]['a_group'][0]['a_image'][0]['s_id']);
    $o_image = new Image($s_link);
    $s_link = $o_image->link();
    $_POST['a_image_upload'][$s_link] = 'save';
    $s_link_temporary = \AImageUpload::linkTemporary($s_link);
    $s_file = File::namePath('Core\\Testing\\Resource\\Transparent.png');
    Drive::save($s_link_temporary, $s_file);

    $o_edit_controller = new EditController();
    $o_edit_controller->id_category = 1;
    $o_edit_controller->k_business = $ar_business->k_business;
    $o_edit_controller->text_annotation = 'text_annotation';
    $o_edit_controller->text_country = 'text_country';
    $o_edit_controller->text_description = 'text_description';
    $o_edit_controller->text_domain = 'test.domain';
    $o_edit_controller->text_email = 'test-milo@mail.ru';
    $o_edit_controller->text_information = 'text_information';
    $o_edit_controller->text_keyword = 'text_keyword';
    $o_edit_controller->text_name = 'text_name';
    $o_edit_controller->text_phone = '333-333-3333';
    $o_edit_controller->text_website = 'website.com';
    $o_edit_controller->save();

    $a_db_resource = db_assoc('
      select
        id_category,
        k_business,
        text_annotation,
        text_country,
        text_description,
        text_domain,
        text_email,
        text_information,
        text_keyword,
        text_name,
        text_phone,
        text_website
      from
        wl_skin_application_resource
      where
        k_business=@k_business
    ',[
      'k_business' => $o_edit_controller->k_business
    ]);

    $a_expect = [
      'id_category' => 1,
      'k_business' => $ar_business->k_business,
      'text_annotation' => 'text_annotation',
      'text_country' => 'text_country',
      'text_description' => 'text_description',
      'text_domain' => 'test.domain',
      'text_email' => 'test-milo@mail.ru',
      'text_information' => 'text_information',
      'text_keyword' => 'text_keyword',
      'text_name' => 'text_name',
      'text_phone' => '333-333-3333',
      'text_website' => 'website.com'
    ];

    $this->assertArrayContains($a_expect, $a_db_resource);

    $s_link = Image::downloadLink($ar_business->k_business, $a_resource[0]['a_group'][0]['a_image'][0]['s_id']);
    $this->assertNotEmpty($s_link);
  }
}

?>