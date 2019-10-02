<?php

namespace Wl\Skin\Application\Resource;

/**
* Text resources for building mobile applications.
 *
 * <dl>
 *   <dt>string <var>dt_update</var></dt>
 *   <dd>
 *     Date and time when this row was updated last time.
 *   </dd>
 *   <dt>int <var>id_category</var></dt>
 *   <dd>
 *     Application category in market.
 *     One of {@link \Wl\Skin\Application\Resource\ApplicationCategorySid} constants.
 *     Value <tt>0</tt> - no category selected.
 *   </dd>
 *   <dt>string <var>k_business</var></dt>
 *   <dd>Business ID for which application will be built.</dd>
 *   <dt>string <var>text_annotation</var></dt>
 *   <dd>Short application description in store.</dd>
 *   <dt>string <var>text_country</var></dt>
 *   <dd>Available countries of application distribution.</dd>
 *   <dt>string <var>text_description</var></dt>
 *   <dd>Full application description in market.</dd>
 *   <dt>string <var>text_domain</var></dt>
 *   <dd>Preferred domain for application in market.</dd>
 *   <dt>string <var>text_email</var></dt>
 *   <dd>Email of business for which application will be built.</dd>
 *   <dt>string <var>text_information</var></dt>
 *   <dd>Other information about application that will be built.</dd>
 *   <dt>string <var>text_keyword</var></dt>
 *   <dd>Application keywords in store.</dd>
 *   <dt>string <var>text_name</var></dt>
 *   <dd>Building application name.</dd>
 *   <dt>string <var>text_phone</var></dt>
 *   <dd>Phone of business for which application will be built.</dd>
 *   <dt>string <var>text_website</var></dt>
 *   <dd>Website of business for which application will be built.</dd>
 * </dl>
 *
 * Last ID: 15.
 */
class ApplicationResourceSql extends \DbUpdateTableModel
{
  /**
   * @inheritDoc
   */
  public function sql()
  {
    return '
      create table wl_skin_application_resource(
        dt_update datetime not null id 11,
        id_category tinyint id 2,
        k_business bigint unsigned id 3,
        text_annotation tinyblob id 4,
        text_country tinyblob id 6,
        text_description blob id 7,
        text_domain tinyblob id 12,
        text_email tinyblob id 14,
        text_information blob id 8,
        text_keyword tinyblob id 9,
        text_name tinyblob id 10,
        text_phone tinyblob id 15,
        text_website tinyblob id 13,

        unique index(k_business),

        foreign key(k_business) references rs_business(k_business) on delete cascade
        ) engine=InnoDB default character set=binary id=1345.7a9b1
    ';
  }
}

?>