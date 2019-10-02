<?php

namespace Wl\Skin\Application\Resource\Test\Image;

use Wl\Skin\Application\Resource\Image;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\Image::downloadLink()} method.
 *
 * @see \Wl\Skin\Application\Resource\Image::downloadLink()
 */
class downloadLink extends \ATestUnit
{
  /**
   * Current business ID.
   *
   * @var string
   */
  private $k_business;

  /**
   * Image ID.
   *
   * @var string
   */
  private $s_id = 'Image Test';

  /**
   * @inheritDoc
   */
  public function setUpBeforeClass()
  {
    $o_fixture = new \DbFixture();
    $ar_business = $o_fixture->rs_business();

    $ar_core_drive_file = $o_fixture->core_drive_file();
    $ar_core_drive_file->s_name = 'test.png';

    $ar_core_drive_link = $o_fixture->core_drive_link();
    $ar_core_drive_link->k_drive_file = $ar_core_drive_file->k_drive_file;

    $ar_core_drive_info = $o_fixture->core_drive_info();
    $ar_core_drive_info->k_drive_file = $ar_core_drive_file->k_drive_file;
    $ar_core_drive_info->id_type =  \Core\Drive\Drive::typeId('image/png');
    $o_fixture->save();

    $ar_core_drive_link->s_link = 'wl.skin.application.resource.image::'.$ar_business->k_business.'-'.$this->s_id;
    $ar_core_drive_link->save_trx();
    $this->k_business = $ar_business->k_business;
  }

  /**
   * Checks that a URL of a specific type is returned.
   */
  public function test()
  {
    $s_link = Image::downloadLink($this->k_business, $this->s_id);
    $s_id = Image::storageLink($this->k_business, $this->s_id);
    $s_expected_link = \Core\Drive\Drive::url($s_id);
    $this->assertStringEquals($s_expected_link, $s_link);
  }
}

?>