<?php

namespace Wl\Skin\Application\Resource\Test\DownloadController;

use Core\All\File;
use Core\Drive\Drive;
use Wl\Backend\Backend;
use Wl\Skin\Application\Resource;
use Wl\Skin\Application\Resource\EditController;
use Wl\Skin\Application\Resource\Image;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\DownloadController::run()} method.
 *
 * @see \Wl\Skin\Application\Resource\DownloadController::run()
 */
class run extends \ATestHttp
{
  /**
   * Tests GET request to the controller.
   */
  public function test()
  {
    $o_fixture = new \DbFixture();
    $ar_login = $o_fixture->passport_login();
    $ar_business = $o_fixture->rs_business();
    $o_fixture->save();

    $this->assertGet('wl-skin-application-resource-downloadcontroller-get-error','backend',Resource\DownloadController::urlController());

    Backend::set($ar_business->k_business);
    passport_session($ar_login->uid);
    passport_privilege_test(passport_uid(),'wl');

    $this->assertGet('wl-skin-application-resource-downloadcontroller-get-ok','ok',Resource\DownloadController::urlController());

    //Adds image to the form.
    $a_resource = EditController::resourceStructure();
    $s_link = Image::applicationLink($ar_business->k_business, $a_resource[0]['a_group'][0]['a_image'][0]['s_id']);
    $o_image = new Image($s_link);
    $s_link = $o_image->link();
    $_POST['a_image_upload'][$s_link] = 'save';
    $s_link_temporary = \AImageUpload::linkTemporary($s_link);
    $s_file = File::namePath('Core\Testing\Resource\Transparent.png');
    Drive::save($s_link_temporary, $s_file);

    //Save added image.
    $o_edit_controller = new EditController();
    $o_edit_controller->k_business = $ar_business->k_business;
    $o_edit_controller->save();

    //Downloads saved image.
    $o_download_controller = new Resource\DownloadController();
    ob_start();
    $o_download_controller->run();
    $s_file = ob_get_contents();
    ob_end_clean();

    //Checks that downloaded file content contains expected file path.
    $this->assertStringContains($a_resource[0]['a_group'][0]['a_image'][0]['a_file'][0], $s_file);
  }
}
?>