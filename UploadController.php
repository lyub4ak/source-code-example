<?php

namespace Wl\Skin\Application\Resource;

use Core\Drive\Drive;
use Wl\Backend\Backend;

/**
 * Uploads images in one *.zip archive for building custom mobile applications.
 */
class UploadController extends \CmsRequestController
{
  /**
   * Errors of images upload.
   *
   * @var array
   */
  private $a_error = [];

  /**
   * Images for application building without screenshots.
   *
   * @var array
   */
  private $a_image = [];

  /**
   * Screenshots for application building.
   *
   * @var array
   */
  private $a_screenshot = [];

  /**
   * <tt>true</tt> - if archive include directories "ios" or "android",
   * <tt>false</tt> - otherwise.
   *
   * @var bool
   */
  private $has_device = false;

  /**
   * ID of a business for which application will be built.
   *
   * Primary key in {@link \RsBusinessSql}.
   *
   * @var string
   */
  private $k_business = 0;

  /**
   * Temporary path for saving files on the server.
   *
   * @var string
   */
  private $s_path = '';

  /**
   * Returns array with uploaded files.
   * This is global variable <var>$_FILES</var> in the real mode and
   * private field {@link MemberImportTest::$a_file} in the test mode.
   *
   * @return array List of the uploaded files.
   */
  protected function _fileArray()
  {
    return $_FILES;
  }

  /**
   * Find images in the pass and upload that if it need.
   * Collects images upload errors in {@link UploadController::$a_error}.
   *
   * @param string $s_path The path where images are searched for.
   * @param int $i_device Key in array {@link EditController::resourceStructure()} which mean device
   * for which application build (0 - iOS, 1 - Android).
   * @param bool $is_screenshot <tt>true</tt> - upload screenshots, <tt>false</tt> - upload other image.
   */
  private function _uploadImage(string $s_path, int $i_device, bool $is_screenshot=false)
  {
    $d_directory = opendir($s_path);
    while(false!==($s_file = readdir($d_directory)))
    {
      if($s_file==='.'||$s_file==='..')
        continue;

      $s_file_full = $s_path.'/'.$s_file;

      if(is_dir($s_file_full))
      {
        if(stripos($s_file, 'app screen') === false && !$is_screenshot)
          $this->_uploadImage($s_file_full, $i_device);
        else
          $this->_uploadImage($s_file_full, $i_device, true);
        continue;
      }

      // Error is returned if current file is not image.
      debug_catch(false, false);
      try
      {
        $i_image = exif_imagetype($s_file_full);
      }
      finally
      {
        debug_catch(true, true);
      }

      if (!$i_image || ($i_image!=IMAGETYPE_PNG && $i_image!=IMAGETYPE_JPEG))
      {
        a_validate_error_add(
          $this->a_error,
          '',
          str_replace($this->s_path.'/','', $s_file_full).' - '.m('This file is not PNG or JPEG format.|wl.skin.application.resource'),
          'not-image'
        );
        continue;
      }

      $a_image_size = getimagesize($s_file_full);
      $is_upload = false;
      if(!$is_screenshot)
        $a_resource = $this->a_image[$i_device]['a_group'];
      else
        $a_resource = $this->a_screenshot[$i_device]['a_group'];

      foreach($a_resource as $i_group=>$a_group)
      {
        if($is_upload)
          break;
        foreach($a_group['a_image'] as $i_image=>$a_image)
        {
          if($a_image['i_width']==$a_image_size[0] && $a_image['i_height']==$a_image_size[1])
          {
            /** @var \Wl\Skin\Application\Resource\Image $o_image */
            $o_image = new Image(Image::applicationLink($this->k_business,$a_image['s_id']));

            $s_link = Image::storageLink($this->k_business, $a_image['s_id']);

            try
            {
              Drive::save($s_link, $s_file_full,null, [
                'can_transparent' => $o_image->transparentAllowGet(),
                'i_height_max' => $o_image->heightMaxGet(),
                'i_height_min' => $o_image->heightMinGet(),
                'i_width_max' => $o_image->widthMaxGet(),
                'i_width_min' => $o_image->widthMinGet(),
                'image-orientation-fix' => true,
                'require-image' => true,
                'timeout' => 0
              ]);

              if(!$is_screenshot)
                unset($this->a_image[$i_device]['a_group'][$i_group]['a_image'][$i_image]);
              else
                unset($this->a_screenshot[$i_device]['a_group'][$i_group]['a_image'][$i_image]);
            }
            catch(\AValidateException $e)
            {
              a_validate_error_add(
                $this->a_error,
                '',
                str_replace($this->s_path.'/','', $s_file_full).' - '.$e->getErrorMessage(),
                $e->getErrorCode()
              );
            }

            $is_upload = true;
            break;
          }
        }
      }

      if(!$is_upload)
      {
        a_validate_error_add(
          $this->a_error,
          '',
          str_replace($this->s_path.'/','', $s_file_full).' - '.m('Image of this size is not need.|wl.skin.application.resource'),
          'excess-image'
        );
      }
    }
  }

  /**
   * Finds directories with images for different devices (iOS and Android).
   * And calls upload method {@link UploadController::_uploadImage()} with need parameters.
   *
   * @param string $s_path The path where images are searched for.
   */
  private function _findDirectory (string $s_path)
  {
    $d_directory = opendir($s_path);
    while(false!==($s_file = readdir($d_directory)))
    {
      if($s_file === '.' || $s_file === '..')
        continue;

      $s_file_full = $s_path.'/'.$s_file;

      // Finds directories of devices "ios" or "android".
      if(is_dir($s_file_full))
      {
        if(stripos($s_file,'ios') !== false)
        {
          $this->_uploadImage($s_file_full,0);
          $this->has_device = true;
        }
        elseif(stripos($s_file,'android') !== false)
        {
          $this->_uploadImage($s_file_full,1);
          $this->has_device = true;
        }
        else
          $this->_findDirectory($s_file_full);
      }
    }
  }

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
   * Sets errors in session and redirect to {@link \Wl\Skin\Application\Resource\EditController}.
   *
   * @throws \AAssertException In a case of assertion.
   */
  private function redirectBack()
  {
    \CmsSession::temporary_set('a_error', serialize($this->a_error), 15);

    if(\ATest::active())
    {
      debug_catch(false, false);
      try
      {
        \MpRedirectOkView::process([
          's_url' => $this->urlBack()
        ]);
      }
      finally
      {
        debug_catch(true, true);
      }
    }
    else
    {
      \MpRedirectOkView::process([
        's_url' => $this->urlBack()
      ]);
    }
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
      return false;
    }

    \ATest::status($this->sid().'-get-ok','ok');

    if(!class_exists('\ZipArchive'))
    {
      a_validate_error_add(
        $this->a_error,
        '',
        m('Unfortunately this installation does not support ZIP packs. Contact the technical team.|wl.skin.application.resource'),
        'pack-install'
      );
      $this->redirectBack();
      return false;
    }

    $a_upload = $this->_fileArray();
    $o_zip = new \ZipArchive();
    if(empty($a_upload['f_pack']['tmp_name']) || $o_zip->open($a_upload['f_pack']['tmp_name']) !== true)
    {
      a_validate_error_add(
        $this->a_error,
        '',
        m('Cannot open uploaded ZIP file.|wl.skin.application.resource'),
        'file-upload'
      );
      $this->redirectBack();
      return false;
    }

    try
    {
      $this->s_path = ALL_PATH_TMP.a_password(16);

      $o_zip->extractTo($this->s_path);
      $o_zip->close();

      $a_resource = EditController::resourceStructure();
      //Sort screenshots and other images.
      foreach ($a_resource as $i_device=>$a_device)
      {
        foreach($a_device['a_group'] as $i_group=>$a_group)
        {
          if($a_group['s_class'] !== 'application-screens')
            $this->a_image[$i_device]['a_group'][] = $a_group;
          else
            $this->a_screenshot[$i_device]['a_group'][] = $a_group;
        }
      }

      $this->_findDirectory($this->s_path);

      if(!$this->has_device)
        a_validate_error_add(
          $this->a_error,
          '',
          m('Directory does not exist directories of devices "ios" or "android".|wl.skin.application.resource'),
          'directory-exist'
        );
    }
    finally
    {
      $this->redirectBack();
      return false;
    }
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