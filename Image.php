<?php

namespace Wl\Skin\Application\Resource;

use Core\Drive\Drive;
use Wl\Backend\Backend;

/**
 * Manages images that are used to build applications.
 */
class Image extends \AImageUploadDrive
{
  /**
   * @inheritDoc
   */
  const HAS_CROP = false;

  /**
   * @inheritDoc
   */
  const PREVIEW_HEIGHT = 100;

  /**
   * @inheritDoc
   */
  const PREVIEW_WIDTH = 100;

  /**
   * Creates a link for the image.
   * Link is used to upload and download image.
   *
   * @param string $k_business Business ID.
   * @param string $s_id Image ID, which consists of the file name and the path to it.
   * Image ID is taken from an array that returns {@link \Wl\Skin\Application\Resource\EditController::resourceStructure()} method.
   * @return string Link for the image.
   */
  public static function applicationLink(string $k_business,string $s_id):string
  {
    return $k_business.'-'.$s_id;
  }

  /**
   * Returns URL to download a file from file storage.
   *
   * @param string $k_business Business ID.
   * @param string $s_id Image ID.
   * @return string URL to download a file from file storage.
   */
  public static function downloadLink(string $k_business, string $s_id):string
  {
    $s_link = Image::storageLink($k_business, $s_id);
    return Drive::url($s_link);
  }

  /**
   * @inheritDoc
   */
  protected function init():void
  {
    $s_link=$this->id();
    \AAssert::notEmpty(preg_match('~^([0-9]+)-([a-zA-Z0-9_]+)$~',$s_link,$a_match),[
      's_link' => $s_link,
      's_message' => 'Image link is invalid.'
    ]);
    $k_business=Backend::get();
    \AAssert::true($k_business&&$a_match[1]==$k_business,[
      'k_business_active' => $k_business,
      'k_business_link' => $a_match[1],
      's_link' => $s_link,
      's_message' => 'Link is from a different business.'
    ]);
    $s_id=$a_match[2];

    $a_resource = EditController::resourceStructure();
    foreach($a_resource as $a_device)
    {
      foreach($a_device['a_group'] as $a_group)
      {
        foreach($a_group['a_image'] as $a_image)
        {
          if($a_image['s_id']===$s_id)
          {
            $this->transparentAllowSet($a_image['can_transparent']);
            $this->heightMaxSet($a_image['i_height']);
            $this->heightMinSet($a_image['i_height']);
            $this->widthMaxSet($a_image['i_width']);
            $this->widthMinSet($a_image['i_width']);
            return;
          }
        }
      }
    }

    \AAssert::fail('Can not find image.',[
      's_link' => $s_link
    ]);
  }

  /**
   * Returns a link to the image in the file storage.
   *
   * @param string $k_business Business ID.
   * @param string $s_id Image ID.
   * @return string Link to the image in the file storage.
   */
  public static function storageLink(string $k_business, string $s_id):string
  {
    $s_id = Image::applicationLink($k_business, $s_id);
    return Image::_unit($s_id);
  }
}

?>