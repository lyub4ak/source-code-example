<?php

namespace Wl\Skin\Application\Resource;

use Core\Drive\Drive;
use Wl\Backend\Backend;

/**
 * Prepares to download images for building custom mobile applications.
 */
class DownloadController extends \CmsRequestController
{
  /**
   * ID of a business for which application will be built.
   *
   * Primary key in {@link \RsBusinessSql}.
   *
   * @var string
   */
  private $k_business = 0;

  /**
   * @inheritDoc
   */
  public function checkGet()
  {
    $this->k_business = Backend::get();

    if(!$this->k_business)
      throw new \AValidateException('backend','You must enter into the backend mode.|wl.skin.application.resource');

    if(!EditController::can())
      throw new \AValidateException('access','Access denied.|wl.skin.application.resource');
  }

  /**
   * Create an icon of specified sizes and return its content.
   *
   * @param int $i_height Height of the created icon.
   * @param int $i_width Width of the created icon.
   * @param string $s_image_content Content of the largest icon in the form.
   * In a case icon of required size is not uploaded, the largest icon in the form is used to create it.
   * @return string Created image content.
   * @throws \AValidateException Can not create resource of image.
   */
  public function iconCreate(int $i_height, int $i_width, string $s_image_content) : string
  {
    // Error is returned if the image type is unsupported, the data is not in a recognised format,
    // or the image is corrupt and cannot be loaded.
    debug_catch(false, false);
    try
    {
      $r_parent_image = imagecreatefromstring($s_image_content);
      if(!$r_parent_image)
        throw new \AValidateException('not-image','Can not create resource of image.|wl.skin.application.resource');
    }
    finally
    {
      debug_catch(true,true);
    }

    imagealphablending($r_parent_image,true);
    list($i_parent_width, $i_parent_height) = getimagesizefromstring($s_image_content);

    // Create new image
    $r_image = imagecreatetruecolor($i_width, $i_height);
    // Install alpha flag and make transparent background
    imagealphablending($r_image,false);
    imagesavealpha($r_image,true);
    $i_background_color = imagecolorallocatealpha($r_image,255,255,255,127);
    imagefill($r_image, 0, 0, $i_background_color);

    // Copy and resize parent image in new resource
    imagecopyresampled($r_image, $r_parent_image, 0, 0, 0, 0, $i_width, $i_height, $i_parent_width, $i_parent_height);

    //Convert GD image to binary data
    // https://stackoverflow.com/questions/2207865/convert-gd-image-back-to-binary-data
    ob_start();
    imagepng($r_image);
    $s_image = ob_get_contents();
    ob_end_clean();

    imagedestroy($r_image);
    return $s_image;
  }

  /**
   * @inheritDoc
   */
  public function run()
  {
    try
    {
      $this->checkGet();
      $s_code = null;
      $s_message = null;
    }
    catch(\AValidateException $e)
    {
      $s_code = $e->getErrorCode();
      $s_message = $e->getErrorMessage();

      \ATest::status($this->sid().'-get-error',$s_code);

      \MpRedirectErrorView::process([
        's_message' => $s_message,
        's_title' => m('error|title;'),
        's_url' => $this->urlBack()
      ]);
      return;
    }

    \ATest::status($this->sid().'-get-ok','ok');

    $s_file_name = $this->k_business.'-customAppResource'; //name of zip file

    $a_file = [];
    $a_resource = EditController::resourceStructure();

    // If the biggest icon is loaded, get its content.
    // This content is used to create missing icons for iOS and Android.
    // An icon is used that has the biggest size of all.
    $a_biggest_icon = end($a_resource[0]['a_group'][0]['a_image']);
    $s_biggest_icon = '';
    if($a_biggest_icon['s_link'])
    {
      $s_biggest_icon_link = Image::storageLink($this->k_business, $a_biggest_icon['s_id']);
      $s_biggest_icon = Drive::download($s_biggest_icon_link);
    }

    foreach($a_resource as $a_out_group)
    {
      foreach($a_out_group['a_group'] as $i_group => $a_group)
      {
        foreach($a_group['a_image'] as $a_image)
        {
          if(empty($a_image['s_link']) )
          {
            // If the biggest icon is loaded and current icon is not loaded in the form, creates current icon.
            if($i_group == 0 && $s_biggest_icon)
            {
              $s_image = $this->iconCreate($a_image['i_width'], $a_image['i_height'], $s_biggest_icon);
              foreach($a_image['a_file'] as $s_file)
                $a_file[$s_file] = $s_image;
            }
            continue;
          }
          $s_link = Image::storageLink($this->k_business, $a_image['s_id']);
          $s_image = Drive::download($s_link);
          foreach($a_image['a_file'] as $s_file)
            $a_file[$s_file] = $s_image;
        }
      }
    }

    if(!$a_file)
    {
      \MpRedirectErrorView::process([
        's_message' => m('No files to upload.|wl.skin.application.resource'),
        's_title' => m('error|title'),
        's_url' => $this->urlBack()
      ]);
      return;
    }

    try
    {
      $s_file_path = ALL_PATH_TMP.microtime().$s_file_name;
      $o_zip = new \ZipArchive();
      $o_zip->open($s_file_path,\ZipArchive::CREATE);
      foreach($a_file as $s_name => $s_content)
        $o_zip->addFromString($s_name, $s_content);
      $o_zip->close();
      $s_file_resource = file_get_contents($s_file_path);
    }
    finally
    {
      unlink($s_file_path);
    }

    if(!\ATest::active())
    {
      header('Content-Description: File Transfer');
      header('Content-Disposition: attachment; filename="'.$s_file_name.'.zip"');
      header('Content-Length: '.strlen($s_file_resource));
      header('Content-Type: application/zip');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
    }

    echo $s_file_resource;
  }

  /**
   * @inheritDoc
   */
  public function urlBack()
  {
    return EditController::urlController();
  }
}
?>