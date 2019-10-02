/**
 * Manages form to upload custom application resources.
 *
 * @constructor
 */
function Wl_Skin_Application_Resource()
{
  // Empty constructor
}

/**
 * Checks if all required content is uploaded to the form.
 */
Wl_Skin_Application_Resource.checkRequireContent = function ()
{
  var jq_form = $('#wl-skin-application-resource-edit-form');
  var has_error = false;

  // Checks form fields.
  var jq_field = jq_form.find('.counter-holder');
  jq_field.each(function()
  {
    var jq_this = $(this);
    if(jq_this.data('require'))
    {
      var jq_element = jq_this.find('input, textarea, select');
      if (!jq_element.val().trim().length)
      {
        has_error = true;
        if(jq_element.is('select'))
          jq_this.find('a.chosen-single').addClass('validate-error');
        else
          jq_element.addClass('validate-error');
      }
    }
  });

  // Checks images.
  var jq_image = jq_form.find('.application-image-load');
  jq_image.each(function()
  {
    var jq_this = $(this);
    if(jq_this.data('require'))
    {
      if(!jq_this.find('.a-image-upload-form').hasClass('image-yes'))
      {
        has_error=true;
        jq_this.addClass('validate-error');
      }
    }
  });

  if(has_error)
    MpNote.show(m('The form does not contain all required resources.|wl.skin.application.resource'),'error');
  else
    MpNote.show(m('The form contain all required resources.|wl.skin.application.resource'),'ok');
};

/**
 * Removes "validate-error" class on image upload.
 *
 * It is call back after image upload.
 *
 * @param {String} s_link Link to image.
 */
Wl_Skin_Application_Resource.removeValidateError = function (s_link)
{
  var jq_form = $('#wl-skin-application-resource-edit-form');
  var jq_element = jq_form.find('.a-image-upload-form[data-link="'+s_link+'"]');
  jq_element.closest('.validate-error').removeClass('validate-error');
};

/**
 * Performs initialization of the form.
 */
Wl_Skin_Application_Resource.startup = function()
{
  var jq_form = $('#wl-skin-application-resource-edit-form');
  a_image_upload_startup(jq_form);

  // Counting the number of characters entered and comparing with the maximum value on load page.
  jq_form.find('input, textarea').each(function()
  {
    var jq_this = $(this);
    Wl_Skin_Application_Resource.symbolCounter(jq_this);
  });

  // Check rules. If no errors then submit form.
  jq_form.submit(function(e)
  {
    if(!AValidateRule.checkForm('wl-skin-application-resource-editcontroller', jq_form))
      e.preventDefault();
  });

  // Counting the number of characters entered and comparing with the maximum value.
  // Removes "validate-error" class for select on change.
  a_form_input_change({
    'call_change': function(jq_field)
    {
      if(jq_field.is('select'))
      {
        jq_field.closest('.counter-holder').find('a.chosen-single').removeClass('validate-error');
        return;
      }
      Wl_Skin_Application_Resource.symbolCounter(jq_field);
    },
    'i_delay': 100,
    'jq_input': jq_form.find('input, textarea, select')
  });
};

/**
 * Updates the quantity of symbols in the field.
 *
 * @param {jQuery} jq_field A field of the form for which the number of characters is updated.
 */
Wl_Skin_Application_Resource.symbolCounter = function (jq_field)
{
  if(jq_field.data('length'))
  {
    var i_current = jq_field.val().trim().length;
    var i_max = parseInt(jq_field.data('length'));
    var jq_symbol_counter = jq_field.closest('.counter-holder').find('.symbol-counter');
    jq_symbol_counter.text(i_current+'/'+i_max);
    jq_field.toggleClass('validate-error', i_current > i_max);
  }
};