<?php

namespace Wl\Skin\Application\Resource;

lib_include_once('rs.microsite');

/**
 * Form to upload and edit resources for building of custom mobile applications.
 */
class EditView extends \CmsTemplateView
{
  /**
   * Prepares variables for insertion into template.
   *
   * @param EditController $o_controller Controller for uploading of resources for custom application.
   * @return array Data to insert into template.
   */
  public function load($o_controller)
  {
    $k_business=$o_controller->k_business;
    $a_resource = EditController::resourceStructure();
    foreach($a_resource as &$a_device)
    {
      foreach($a_device['a_group'] as &$a_group)
      {
        foreach($a_group['a_image'] as &$a_image)
        {
          $o_image=new Image(Image::applicationLink($k_business, $a_image['s_id']));
          $a_image['o_image'] = $o_image;
        }
        unset($a_image);
      }
      unset($a_group);
    }
    unset($a_device);

    if(!$o_controller->postIs())
    {
      $a_data = db_assoc('
        select
          id_category,
          text_annotation,
          text_country,
          text_description,
          text_email,
          text_domain,
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
        'k_business' => $k_business
      ]);
      if(!$a_data)
        $a_data=$o_controller->postArray();
    }
    else
      $a_data=$o_controller->postArray();

    // If contact information is not set load it from data base.
    if(!$a_data['text_email'] || !$a_data['text_phone'])
    {
      $a_location = db_assoc_all('
        select
          s_email,
          s_phone
        from
          rs_location
        where
          k_business=@k_business
      ',[
        'k_business' => $k_business
      ]);

      $a_data['text_email'] = $a_data['text_email'] ?: $a_location[0]['s_email'];
      $a_data['text_phone'] = $a_data['text_phone'] ?: $a_location[0]['s_phone'];
    }

    if(!$a_data['text_website'])
      $a_data['text_website'] = rs_microsite_url('',$k_business);

    /** @see \Wl\Skin\Application\Resource\UploadController::$a_error */
    $a_error = unserialize(\CmsSession::temporary_get('a_error')) ?: [];

    return [
      'a_error' => $a_error['error'] ?? [],
      'a_resource' => $a_resource,
      'has_error' => $a_error['has-error'] ?? false,
      'html_annotation' => htmlspecialchars($a_data['text_annotation']),
      'html_country' => htmlspecialchars($a_data['text_country']),
      'html_description' => htmlspecialchars($a_data['text_description']),
      'html_domain' => htmlspecialchars($a_data['text_domain']),
      'html_email' => htmlspecialchars($a_data['text_email']),
      'html_information' => htmlspecialchars($a_data['text_information']),
      'html_keyword' => htmlspecialchars($a_data['text_keyword']),
      'html_name' => htmlspecialchars($a_data['text_name']),
      'html_phone' => htmlspecialchars($a_data['text_phone']),
      'html_website' => htmlspecialchars($a_data['text_website']),
      'id_category' => htmlspecialchars($a_data['id_category']),
      'o_controller' => $o_controller,
      'url_action' => htmlspecialchars($o_controller::urlController()),
      'url_upload' => htmlspecialchars(UploadController::urlController()),
      'url_zip' => htmlspecialchars(DownloadController::urlController())
    ];
  }

  /**
   * @inheritDoc
   */
  protected function place($o_controller)
  {
    mp_title(m('Resources for custom application|wl.skin.application.resource'));
  }
}

?>