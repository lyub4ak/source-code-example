<?php

namespace Wl\Skin\Application\Resource\Test\Image;

use Wl\Skin\Application\Resource\Image;

/**
 * Unit test for {@link \Wl\Skin\Application\Resource\Image::applicationLink()} method.
 *
 * @see \Wl\Skin\Application\Resource\Image::applicationLink()
 */
class applicationLink extends \ATestUnit
{
  /**
   * Checks that the method returns a link of specific format.
   */
  public function test()
  {
    $k_business = 1;
    $s_id = 'test_image_name';
    $this->assertStringEquals(Image::applicationLink($k_business, $s_id), '1-test_image_name');
  }
}

?>