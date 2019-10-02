<?php

namespace Wl\Skin\Application\Resource\Test\Image;

use Wl\Skin\Application\Resource;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\Image::storageLink()} method.
 *
 * @see \Wl\Skin\Application\Resource\Image::storageLink()
 */
class storageLink extends \ATestUnit
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
    $o_fixture->save();

    $this->k_business = $ar_business->k_business;
  }

  /**
   * Checks that a link of a specific type is returned.
   */
  public function test()
  {
    $s_link = Resource\Image::storageLink($this->k_business, $this->s_id);
    $s_expected_link = 'wl.skin.application.resource.image::'.$this->k_business.'-'.$this->s_id;
    $this->assertStringEquals($s_expected_link, $s_link);
  }
}

?>