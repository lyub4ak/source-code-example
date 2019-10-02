<?php

namespace Wl\Skin\Application\Resource;

use ADate;
use Core\Passport\Privilege\PassportPrivilege;
use Wl\Backend\Backend;

/**
 * Saves information for an application to post into Google Play and App store.
 *
 * There is a need to prepare a large set of images for compiling mobile applications for Google Play and App store.
 * Images must match the requirements (sizes, types, contents, file names).
 * For designers, it is difficult to do this. Therefore, we made this form so that designers would upload everything
 * here and all formal requirements would be checked automatically.
 */
class EditController extends \CmsRequestController
{
  /**
   * Maximum length of field annotation.
   *
   * @see EditController::$text_annotation
   */
  const ANNOTATION_MAX_LENGTH = 80;

  /**
   * Maximum length of field business.
   *
   * @see EditController::$text_business
   */
  const BUSINESS_MAX_LENGTH = 4000;

  /**
   * Maximum length of field country.
   *
   * @see EditController::$text_country
   */
  const COUNTRY_MAX_LENGTH = 400;

  /**
   * Maximum length of field description.
   *
   * @see EditController::$text_description
   */
  const DESCRIPTION_MAX_LENGTH = 4000;

  /**
   * Maximum length of preferred domain field.
   *
   * @see EditController::$text_domain
   */
  const DOMAIN_MAX_LENGTH = 30;

  /**
   * Maximum length of field information.
   *
   * @see EditController::$text_information
   */
  const INFORMATION_MAX_LENGTH = 4000;

  /**
   * Maximum length of field keyword.
   *
   * @see EditController::$text_keyword
   */
  const KEYWORD_MAX_LENGTH = 100;

  /**
   * Maximum length of field name.
   *
   * @see EditController::$text_name
   */
  const NAME_MAX_LENGTH = 30;

  /**
   * @inheritDoc
   */
  const REDIRECT_OK_SHOW = true;

  /**
   * @inheritDoc
   */
  const REQUIRE_USER = true;

  /**
   * Maximum length of field website.
   *
   * @see EditController::$text_website
   */
  const WEBSITE_MAX_LENGTH = 150;

  /**
   * Application category in market.
   *
   * One of {@link \Wl\Skin\Application\Resource\ApplicationCategorySid} constants.
   * Value <tt>0</tt> - no category selected.
   *
   * @post
   * @rule type-zid
   * @var int
   */
  public $id_category = 0;

  /**
   * ID of a business for which application will be built.
   *
   * Primary key in {@link \RsBusinessSql}.
   *
   * @var string
   */
  public $k_business = 0;

  /**
   * Short application description in store.
   *
   * @decorator trim
   * @post
   * @rule length-max ANNOTATION_MAX_LENGTH
   * @var string
   * @see EditController::ANNOTATION_MAX_LENGTH
   */
  public $text_annotation = '';

  /**
   * Available countries of application distribution.
   *
   * Comma-separated list of countries.
   *
   * @decorator trim
   * @post
   * @rule length-max COUNTRY_MAX_LENGTH
   * @var string
   * @see EditController::COUNTRY_MAX_LENGTH
   */
  public $text_country = '';

  /**
   * Full application description in market.
   *
   * @decorator trim
   * @post
   * @rule length-max DESCRIPTION_MAX_LENGTH
   * @var string
   * @see EditController::DESCRIPTION_MAX_LENGTH
   */
  public $text_description = '';

  /**
   * Preferred domain for custom application.
   *
   * @decorator trim
   * @post
   * @rule length-max DOMAIN_MAX_LENGTH
   * @var string
   * @see EditController::DOMAIN_MAX_LENGTH
   */
  public $text_domain = '';

  /**
   * Email which need add to the store for custom application.
   * On default use email of random location.
   *
   * @decorator trim
   * @post
   * @rule mail-valid
   * @var string
   * @see EditController::EMAIL_MAX_LENGTH
   */
  public $text_email = '';

  /**
   * Other information about application that will be built.
   *
   * @decorator trim
   * @post
   * @rule length-max INFORMATION_MAX_LENGTH
   * @var string
   * @see EditController::INFORMATION_MAX_LENGTH
   */
  public $text_information = '';

  /**
   * Application keywords in store.
   *
   * @decorator trim
   * @post
   * @rule length-max KEYWORD_MAX_LENGTH
   * @var string
   * @see EditController::KEYWORD_MAX_LENGTH
   */
  public $text_keyword = '';

  /**
   * Name of the application.
   *
   * @decorator trim
   * @post
   * @rule length-max NAME_MAX_LENGTH
   * @var string
   * @see EditController::NAME_MAX_LENGTH
   */
  public $text_name = '';

  /**
   * Phone which need add to the store for custom application.
   * On default use phone of random location.
   *
   * @decorator trim
   * @post
   * @rule phone-valid
   * @var string
   * @see EditController::PHONE_MAX_LENGTH
   */
  public $text_phone = '';

  /**
   * Website which need add to the store for custom application.
   * On default use business microsite.
   *
   * @decorator trim
   * @post
   * @rule length-max WEBSITE_MAX_LENGTH
   * @var string
   * @see EditController::WEBSITE_MAX_LENGTH
   */
  public $text_website = '';

  /**
   * Checks if current user has privilege access the form to upload application resources.
   *
   * @return bool Whether current user has access to the form to upload application resources.
   */
  public static function can():bool
  {
    return PassportPrivilege::currentHas('wl.skin.application.resource');
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
   * @inheritDoc
   */
  public function checkPost()
  {
    if($this->id_category!=0 && !ApplicationCategorySid::id_exists($this->id_category))
      $this->errorAdd('category-nx','id_category');

    if($this->text_website && !preg_match('~^[A-Za-z0-9/:\\.?_=&]{5,}$~', $this->text_website))
      $this->errorAdd('url-invalid','text_website', 'Website is not valid.|wl.skin.application.resource');
  }

  /**
   * Describes images required for mobile application, and specifies their structure in form.
   *
   * @return array[] Description of required images and their structure. Each element of this array contains a list of
   *   image groups within one application provider (Google Play or App Store). Structure of one element is the
   *   following:<dl>
   *   <dt>array[] <var>a_group</var></dt>
   *   <dd>
   *     Groups of images, such as icons, loading screens or screenshots. One element of this array is an array that
   *     contains:<dl>
   *       <dt>array[] <var>a_image</var></dt>
   *       <dd>
   *         A list of images in the group. One element contains parameters of an image:<dl>
   *           <dt>string[] <var>a_file</var></dt>
   *           <dd>A list of fully qualified file names. This name is used to save files in final archive.</dd>
   *           <dt>bool <var>can_transparent</var></dt>
   *           <dd>
   *             Specified if this image can have transparent pixels.
   *             <tt>true</tt> if it is allowed that there be transparent pixels.
   *             <tt>false</tt> if it is required that all pixels be opaque.
   *           </dd>
   *           <dt>int <var>i_height</var></dt>
   *           <dd>Required image height.</dd>
   *           <dt>int <var>i_width</var></dt>
   *           <dd>Required image width.</dd>
   *           <dt>bool <var>is_require</var></dt>
   *           <dd>Whether this image is required.</dd>
   *         </dl>
   *       </dd>
   *       <dt>string <var>s_class</var></dt>
   *       <dd>CSS class for this images block.</dd>
   *       <dt>string <var>text_comment</var></dt>
   *       <dd>Comment for images block.</dd>
   *       <dt>string <var>text_subtitle</var></dt>
   *       <dd>Subtitle for images block.</dd>
   *       <dt>string <var>text_title</var></dt>
   *       <dd>Title for images block.</dd>
   *     </dl>
   *   </dd>
   *   <dt>string <var>text_group</var></dt>
   *   <dd>Title of the group.</dd>
   * </dl>
   *
   * @link https://docs.google.com/document/d/1OP938ObMnTV3x_P0r_sLcgDGz398IHo7imNgfs-CrFE/edit# Instructions for building a new application
   * @link https://support.google.com/googleplay/android-developer/answer/1078870?hl=ru Graphic objects for building Android application
   * @link https://help.apple.com/itunes-connect/developer/?lang=en#/devd274dd925 Graphic objects for building iOS application
   * @link https://github.com/phonegap/phonegap/wiki/App-Icon-Sizes Application icons sizes
   * @throws \AAssertException in a case of assertion.
   */
  public static function resourceStructure():array
  {
    static $a_resource=null;

    if($a_resource===null||\ATest::active())
    {
      $a_resource = [
        // Resources for iOS.
        [
          'a_group' => [
            // Icons for building iOS application.
            [
              'a_image' => [
                [
                  'a_file' => ['ios/icons/icon-small.png'],
                  'can_transparent' => true,
                  'i_height' => 29,
                  'i_width' => 29,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-40.png'],
                  'can_transparent' => true,
                  'i_height' => 40,
                  'i_width' => 40,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-small@2x.png'],
                  'can_transparent' => true,
                  'i_height' => 58,
                  'i_width' => 58,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-76.png'],
                  'can_transparent' => true,
                  'i_height' => 76,
                  'i_width' => 76,
                  'is_require' => false
                ],
                [
                  'a_file' => [
                    'ios/icons/icon-20@1x.png',
                    'ios/icons/icon-20@1x-1.png',
                    'ios/icons/icon-20@2x.png',
                    'ios/icons/icon-20@2x-1.png',
                    'ios/icons/icon-40@2x.png',
                    'ios/icons/icon-40@2x-1.png'
                  ],
                  'can_transparent' => true,
                  'i_height' => 80,
                  'i_width' => 80,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-small@3x.png'],
                  'can_transparent' => true,
                  'i_height' => 87,
                  'i_width' => 87,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-60@2x.png'],
                  'can_transparent' => true,
                  'i_height' => 120,
                  'i_width' => 120,
                  'is_require' => false
                ],
                [
                  'a_file' => [
                    'ios/icons/icon.png',
                    'ios/icons/icon@2x.png',
                    'ios/icons/icon-50.png',
                    'ios/icons/icon-50@2x.png',
                    'ios/icons/icon-60.png',
                    'ios/icons/icon-72.png',
                    'ios/icons/icon-72@2x.png'
                  ],
                  'can_transparent' => true,
                  'i_height' => 140,
                  'i_width' => 140,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-76@2x.png'],
                  'can_transparent' => true,
                  'i_height' => 152,
                  'i_width' => 152,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-83.5@2x.png'],
                  'can_transparent' => true,
                  'i_height' => 167,
                  'i_width' => 167,
                  'is_require' => false
                ],
                [
                  'a_file' => ['ios/icons/icon-60@3x.png'],
                  'can_transparent' => true,
                  'i_height' => 180,
                  'i_width' => 180,
                  'is_require' => true
                ],
              ],
              's_class' => 'icon-application',
              'text_comment' => m('Please, upload files in PNG format.|wl.skin.application.resource'),
              'text_subtitle' => '',
              'text_title' => m('Icons for building iOS application.|wl.skin.application.resource')
            ],

            // Loading screens for building iOS application.
            [
              'a_image' => [
                [
                  'a_file' => ['ios/loadingscreens/Default@2x~iphone.png'],
                  'can_transparent' => true,
                  'i_height' => 960,
                  'i_width' => 640,
                  'is_require' => true
                ],
                [
                  'a_file' => ['ios/loadingscreens/Default-568h@2x~iphone.png'],
                  'can_transparent' => true,
                  'i_height' => 1136,
                  'i_width' => 640,
                  'is_require' => true
                ],
                [
                  'a_file' => ['ios/loadingscreens/Default-667h.png'],
                  'can_transparent' => true,
                  'i_height' => 1334,
                  'i_width' => 750,
                  'is_require' => true
                ],
                [
                  'a_file' => ['ios/loadingscreens/Default-Portrait~ipad.png'],
                  'can_transparent' => true,
                  'i_height' => 1024,
                  'i_width' => 768,
                  'is_require' => true
                ],
                [
                  'a_file' => ['ios/loadingscreens/Default-736h.png'],
                  'can_transparent' => true,
                  'i_height' => 2208,
                  'i_width' => 1242,
                  'is_require' => true
                ],
                [
                  'a_file' => ['ios/loadingscreens/Default-Portrait@2x~ipad.png'],
                  'can_transparent' => true,
                  'i_height' => 2048,
                  'i_width' => 1536,
                  'is_require' => true
                ]
              ],
              's_class' => 'loading-screens',
              'text_comment' => m('Please, upload a file in PNG format.|wl.skin.application.resource'),
              'text_subtitle' => '',
              'text_title' => m('Loading screens for iOS application.|wl.skin.application.resource')
            ],

            // Icon for uploading iOS application on iTunes.
            [
              'a_image' => [
                [
                  'a_file' => ['ios/screenshots/icon-iTunes.png'],
                  'can_transparent' => false,
                  'i_height' => 1024,
                  'i_width' => 1024,
                  'is_require' => true
                ],
              ],
              's_class' => 'icon-store',
              'text_comment' => m('Please, upload a file in JPEG or PNG format, without alpha-chanel, transparent area and rounded corners.|wl.skin.application.resource'),
              'text_subtitle' => m('Icon|wl.skin.application.resource'),
              'text_title' => m('Resources for uploading application on iTunes.|wl.skin.application.resource')
            ],

            // iPhone's screenshots for uploading iOS application on iTunes.
            [
              'a_image' => EditController::screenshotImage(2208, 1242, 'ios/screenshots/1242x2208/screenshot-'),
              's_class' => 'application-screens',
              'text_comment' => m('
                Please, upload a high-quality JPEG or PNG image, without alpha-chanel and transparent area.
                From 2 to 5 pieces every size.|wl.skin.application.resource
              '),
              'text_subtitle' => m('Screenshots|wl.skin.application.resource'),
              'text_title' => ''
            ],

            // iPad's screenshots for uploading iOS application on iTunes.
            [
              'a_image' => EditController::screenshotImage(2732, 2048, 'ios/screenshots/2048x2732/screenshot-'),
              's_class' => 'application-screens',
              'text_comment' => '',
              'text_subtitle' => '',
              'text_title' => ''
            ],
          ],
          'text_group' => m('Resources for iOS application|wl.skin.application.resource'),
        ],

        // Resources for Android.
        [
          'a_group' => [
            // Icons for building Android application.
            [
              'a_image' => [
                [
                  'a_file' => ['Android/icons/icon-36-ldpi.png'],
                  'can_transparent' => true,
                  'i_height' => 36,
                  'i_width' => 36,
                  'is_require' => false
                ],
                [
                  'a_file' => ['Android/icons/icon-48-mdpi.png'],
                  'can_transparent' => true,
                  'i_height' => 48,
                  'i_width' => 48,
                  'is_require' => false
                ],
                [
                  'a_file' => ['Android/icons/icon-72-hdpi.png'],
                  'can_transparent' => true,
                  'i_height' => 72,
                  'i_width' => 72,
                  'is_require' => false
                ],
                [
                  'a_file' => ['Android/icons/icon-96-xhdpi.png'],
                  'can_transparent' => true,
                  'i_height' => 96,
                  'i_width' => 96,
                  'is_require' => false
                ]
              ],
              's_class' => 'icon-application',
              'text_comment' => m('Please, upload files in PNG format.|wl.skin.application.resource'),
              'text_subtitle' => '',
              'text_title' => m('Icons for building Android application.|wl.skin.application.resource')
            ],

            // Loading screens for building Android application.
            [
              'a_image' => [
                [
                  'a_file' => ['Android/loadingscreens/splash-ldpi-portrait.png'],
                  'can_transparent' => true,
                  'i_height' => 320,
                  'i_width' => 200,
                  'is_require' => true
                ],
                [
                  'a_file' => ['Android/loadingscreens/splash-mdpi-portrait.png'],
                  'can_transparent' => true,
                  'i_height' => 480,
                  'i_width' => 320,
                  'is_require' => true
                ],
                [
                  'a_file' => ['Android/loadingscreens/splash-hdpi-portrait.png'],
                  'can_transparent' => true,
                  'i_height' => 800,
                  'i_width' => 480,
                  'is_require' => true
                ],
                [
                  'a_file' => ['Android/loadingscreens/splash-xhdpi-portrait.png'],
                  'can_transparent' => true,
                  'i_height' => 1280,
                  'i_width' => 720,
                  'is_require' => true
                ]
              ],
              's_class' => 'loading-screens',
              'text_comment' => m('Please, upload a file in PNG format.|wl.skin.application.resource'),
              'text_subtitle' => '',
              'text_title' => m('Loading screens for Android application.|wl.skin.application.resource')
            ],

            // Icon for uploading Android application on Google Play.
            [
              'a_image' => [
                [
                  'a_file' => ['Android/screenshots/icon-GooglePlay.png'],
                  'can_transparent' => true,
                  'i_height' => 512,
                  'i_width' => 512,
                  'is_require' => true
                ],
              ],
              's_class' => 'icon-store',
              'text_comment' => m('Please, upload a file in JPEG or PNG format.|wl.skin.application.resource'),
              'text_subtitle' => m('Icon|wl.skin.application.resource'),
              'text_title' => m('Resources for uploading application on Google Play.|wl.skin.application.resource')
            ],

            // Feature graphic for uploading Android application on Google Play.
            [
              'a_image' => [
                [
                  'a_file' => ['Android/screenshots/featureGraphic-GooglePlay.png'],
                  'can_transparent' => false,
                  'i_height' => 500,
                  'i_width' => 1024,
                  'is_require' => true
                ],
              ],
              's_class' => 'icon-store',
              'text_comment' => m('Please, upload a file in PNG format without alpha-chanel and transparent area.|wl.skin.application.resource'),
              'text_subtitle' => m('Feature Graphic|wl.skin.application.resource'),
              'text_title' => ''
            ],

            // Phone's screenshots for uploading Android application on Google Play.
            [
              'a_image' => EditController::screenshotImage(2208, 1242, 'Android/screenshots/1242x2208/screenshot-'),
              's_class' => 'application-screens',
              'text_comment' => m('
                Please, upload a high-quality JPEG or PNG image, without alpha-chanel and transparent area.
                From 2 to 5 pieces every size.|wl.skin.application.resource
              '),
              'text_subtitle' => m('Screenshots|wl.skin.application.resource'),
              'text_title' => ''
            ],

            // Small tablet's screenshots for uploading Android application on Google Play.
            [
              'a_image' => EditController::screenshotImage(2048, 1536, 'Android/screenshots/1536x2048/screenshot-'),
              's_class' => 'application-screens',
              'text_comment' => '',
              'text_subtitle' => '',
              'text_title' => ''
            ],

            // Large tablet's screenshots for uploading Android application on Google Play.
            [
              'a_image' => EditController::screenshotImage(2732, 2048, 'Android/screenshots/2048x2732/screenshot-'),
              's_class' => 'application-screens',
              'text_comment' => '',
              'text_subtitle' => '',
              'text_title' => ''
            ],
          ],
          'text_group' => m('Resources for Android application|wl.skin.application.resource'),
        ],
      ];

      $a_id=[];
      $k_business=Backend::get();
      \AAssert::notEmpty($k_business);

      foreach($a_resource as &$a_device)
      {
        foreach($a_device['a_group'] as &$a_group)
        {
          foreach($a_group['a_image'] as &$a_image)
          {
            $s_id=preg_replace('~[^a-zA-Z0-9_]+~','_',$a_image['a_file'][0]);

            \AAssert::true(!isset($a_id[$s_id]),[
              's_file' => $a_image['a_file'][0],
              's_id' => $s_id,
              's_message' => 'Image identifier is not unique.'
            ]);
            $a_id[$s_id]=true;

            $a_image['s_id'] = $s_id;
            $a_image['s_link'] = Image::downloadLink($k_business, $s_id);
          }
          unset($a_image);
          $a_group['html_comment'] = htmlspecialchars($a_group['text_comment']);
          $a_group['html_subtitle'] = htmlspecialchars($a_group['text_subtitle']);
          $a_group['html_title'] = htmlspecialchars($a_group['text_title']);
        }
        unset($a_group);
        $a_device['html_group'] = htmlspecialchars($a_device['text_group']);
      }
      unset($a_device);
    }
    return $a_resource;
  }

  /**
   * @inheritDoc
   */
  public function save()
  {
    db_query('
      insert into
        wl_skin_application_resource
      set
        dt_update=@dt_update,
        id_category=@id_category,
        k_business=@k_business,
        text_annotation=@text_annotation,
        text_country=@text_country,
        text_description=@text_description,
        text_domain=@text_domain,
        text_email=@text_email,
        text_information=@text_information,
        text_keyword=@text_keyword,
        text_name=@text_name,
        text_phone=@text_phone,
        text_website=@text_website
      on duplicate key update
        dt_update=values(dt_update),
        id_category=values(id_category),
        text_annotation=values(text_annotation),
        text_country=values(text_country),
        text_description=values(text_description),
        text_domain=values(text_domain),
        text_email=values(text_email),
        text_information=values(text_information),
        text_keyword=values(text_keyword),
        text_name=values(text_name),
        text_phone=values(text_phone),
        text_website=values(text_website)
    ',[
      'dt_update' => ADate::nowMysql(),
      'id_category' => $this->id_category,
      'k_business' => $this->k_business,
      'text_annotation' => $this->text_annotation,
      'text_country' => $this->text_country,
      'text_description' => $this->text_description,
      'text_email' => $this->text_email,
      'text_domain' => preg_replace('~[_]+~', '.', $this->text_domain),
      'text_information' => $this->text_information,
      'text_keyword' => $this->text_keyword,
      'text_name' => $this->text_name,
      'text_phone' => $this->text_phone,
      'text_website' => $this->text_website
    ]);

    // Save images.
    $a_resource = EditController::resourceStructure();
    foreach ($a_resource as $a_out_group)
    {
      foreach($a_out_group['a_group'] as $a_group)
      {
        foreach($a_group['a_image'] as $a_image)
        {
          /** @var \Wl\Skin\Application\Resource\Image $o_image */
          $o_image = new Image(Image::applicationLink($this->k_business, $a_image['s_id']));
          $o_image->save(Image::applicationLink($this->k_business, $a_image['s_id']));
        }
      }
    }
  }

  /**
   * Creates a structure for five equal images.
   *
   * These images are used in application screenshots for stores.
   *
   * The first two images are required, others are not. This is requirement of stores.
   *
   * @param int $i_height Height of images.
   * @param int $i_width Width of images.
   * @param string $s_file Prefix for image file names.
   * @return array Structure for five equal images.
   */
  private static function screenshotImage(int $i_height, int $i_width, string $s_file) : array
  {
    $a_image = [];
    for($i = 0; $i < 5; $i++)
    {
      $a_image[] = [
        'a_file' => [$s_file.($i+1).'.png'],
        'can_transparent' => false,
        'i_height' => $i_height,
        'i_width' => $i_width,
        'is_require' => $i<2
      ];
    }
    return $a_image;
  }
}

?>