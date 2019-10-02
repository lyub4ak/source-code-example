<?php

namespace Wl\Skin\Application\Resource\Test\UploadController;

use Core\All\File;
use Core\Drive\Drive;
use Wl\Backend\Backend;
use Wl\Skin\Application\Resource;
use Wl\Skin\Application\Resource\Image;
use Wl\Skin\Application\Resource\UploadController;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\UploadController::run()} method.
 *
 * @see \Wl\Skin\Application\Resource\UploadController::run()
 */
class run extends \ATestHttp
{
  /**
   * Tests GET request to the controller.
   *
   * Uploads images from archive.
   *
   * Checks uploaded images.
   *
   * Checks errors.
   */
  public function test()
  {
    $o_fixture = new \DbFixture();
    $ar_login = $o_fixture->passport_login();
    $ar_business = $o_fixture->rs_business();
    $o_fixture->save();

    $this->assertGet('wl-skin-application-resource-uploadcontroller-get-error','backend',UploadController::urlController());

    Backend::set($ar_business->k_business);
    passport_session($ar_login->uid);
    passport_privilege_test(passport_uid(),'wl');

    $this->assertGet('wl-skin-application-resource-uploadcontroller-get-ok','ok',UploadController::urlController());

    // Sets path for *.zip file.
    $_FILES['f_pack']['tmp_name'] = File::namePath('Wl\Skin\Application\Resource\Test\UploadController\test.zip');

    // Uploads images from archive.
    $o_controller = new UploadController();
    $o_controller->run();

    // Checks uploaded images:
    $a_resource = Resource\EditController::resourceStructure();

    // Icon for ios.
    $s_link = Image::storageLink($ar_business->k_business, $a_resource[0]['a_group'][0]['a_image'][0]['s_id']);
    $a_file = Drive::file($s_link);
    $a_file_expect = [
      'i_height' => $a_resource[0]['a_group'][0]['a_image'][0]['i_height'],
      'i_width' => $a_resource[0]['a_group'][0]['a_image'][0]['i_width'],
      'id_type' => Drive::typeId('image/png')
    ];
    $this->assertArrayContains($a_file_expect, $a_file);

    // Screenshot for ios.
    $s_link = Image::storageLink($ar_business->k_business, $a_resource[0]['a_group'][3]['a_image'][0]['s_id']);
    $a_file = Drive::file($s_link);
    $a_file_expect = [
      'i_height' => $a_resource[0]['a_group'][3]['a_image'][0]['i_height'],
      'i_width' => $a_resource[0]['a_group'][3]['a_image'][0]['i_width'],
      'id_type' => Drive::typeId('image/jpeg')
    ];
    $this->assertArrayContains($a_file_expect, $a_file);

    // Icon for android.
    $s_link = Image::storageLink($ar_business->k_business, $a_resource[1]['a_group'][0]['a_image'][0]['s_id']);
    $a_file = Drive::file($s_link);
    $a_file_expect = [
      'i_height' => $a_resource[1]['a_group'][0]['a_image'][0]['i_height'],
      'i_width' => $a_resource[1]['a_group'][0]['a_image'][0]['i_width'],
      'id_type' => Drive::typeId('image/png')
    ];
    $this->assertArrayContains($a_file_expect, $a_file);

    // Screenshot for android.
    $s_link = Image::storageLink($ar_business->k_business, $a_resource[1]['a_group'][4]['a_image'][0]['s_id']);
    $a_file = Drive::file($s_link);
    $a_file_expect = [
      'i_height' => $a_resource[1]['a_group'][4]['a_image'][0]['i_height'],
      'i_width' => $a_resource[1]['a_group'][4]['a_image'][0]['i_width'],
      'id_type' => Drive::typeId('image/jpeg')
    ];
    $this->assertArrayContains($a_file_expect, $a_file);

    // Checks errors.
    $text_error_a = 'test/android/not need android size.png - Image of this size is not need.';
    $text_error_b = 'test/ios/test.txt - This file is not PNG or JPEG format.';
    $text_error_c = 'test/ios/transparent.png - It is not allowed to upload transparent images.';
    $has_error_a = false;
    $has_error_b = false;
    $has_error_c = false;
    $a_error = unserialize(\CmsSession::temporary_get('a_error'));
    foreach($a_error['error'] as $a_error_current)
    {
      if(strrpos($a_error_current['message'], $text_error_a) !== false)
        $has_error_a = true;

      if(strrpos($a_error_current['message'], $text_error_b) !== false)
        $has_error_b = true;

      if(strrpos($a_error_current['message'], $text_error_c) !== false)
        $has_error_c = true;
    }

    $this->assertTrue($has_error_a, [
      'message' => '"'.$text_error_a.'" - error is not set.'
    ]);

    $this->assertTrue($has_error_b, [
      'message' => '"'.$text_error_b.'" - error is not set.'
    ]);

    $this->assertTrue($has_error_c, [
      'message' => '"'.$text_error_c.'" - error is not set.'
    ]);
  }
}

?>