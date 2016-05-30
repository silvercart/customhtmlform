# How to extend a CustomHtmlForm

SilverCart depends on CustomHtmlForm, a module we designed for working with SilverStripe. It gives you full control over your forms markup, validation and javascripting. Simply define a form via a multidimensional array, define an action method, create a unique template and inject the form in any controller. Custom markup for form field types is optional.

This documentation describes the possibilities to extend an existing CustomHtmlForm using the [SilverStripe extension pattern](https://docs.silverstripe.org/en/3.3/developer_guides/extending/extensions/). Also a short tutorial describes how to extend an existing CustomHtmlForm by adding new form fields to SilverCart's SilvercartAddAddressForm.

If you are new to this topic you should also have a look to the documentation “How to implement a CustomHtmlForm”.

## Special Methods to extend CustomHtmlForm
- - -

There are a few methods that can be used by a DataObjectDecorator to extend a CustomHtmlForm.
 
These methods are: 

* **public function onAfterSubmitFailure(&$data, &$form);**
This method will be called after CustomHtmlForm's default submitFailure. You can manipulate the relevant data here.

* **public function onAfterSubmitSuccess(&$data, &$form, &$formData);**
This method will be called after CustomHtmlForm's default submitSuccess. You can manipulate the relevant data here.

* **public function onBeforeSubmitFailure(&$data, &$form);**
This method will be called before CustomHtmlForm's default submitFailure. You can manipulate the relevant data here.

* **public function onBeforeSubmitSuccess(&$data, &$form, &$formData);**
This method will be called before CustomHtmlForm's default submitSuccess. You can manipulate the relevant data here.

* **public function overwriteSubmitFailure(&$data, &$form);**
This method will replace CustomHtmlForm's default submitFailure. It's important that this method returns sth. to ensure that the default submitFailure won't be called. The return value should be a rendered template or sth. similar. You can also trigger a direct or redirect and return what ever you want (perhaps boolean true?).

* **public function overwriteSubmitSuccess(&$data, &$form, &$formData);**
This method will replace CustomHtmlForm's default submitSuccess. It's important that this method returns sth. to ensure that the default submitSuccess won't be called. The return value should be a rendered template or sth. similar. You can also trigger a direct or redirect and return what ever you want (perhaps boolean true?).

* **public function updateFormFields(&$formFields);**
This method is called before CustomHtmlForm requires the form fields. You can manipulate the default form fields here.


## Tutorial: Extend an existing CustomHtmlForm
- - -

This tutorial describes the way to add custom form fields to SilverCart's form to add new addresses, SilvercartAddAddressForm.

### What do I need to do that?

Well, we use SilverStripe extensions to extend the forms, so you need a extension first. ![;-)](_images/icon_wink.gif)

In fact, you need two extensions. One to extend the form itself and one to extend the SilvercartAddress object, to reflect the additional fields to the data model.

Create the files “MySilvercartAddAddressFormExtension.php” and “MySilvercartAddressExtension.php” in your projects code directory. This tutorial uses “mysite” as project directory.

You will also need a new form template that includes the markup and template engine calls for the new form fields.

Therefor copy SilverCart's default template “SilvercartAddAddressForm.ss” out of ”/silvercart/templates/Layout” into your projects template directory.

Your silverstripe directory structure should look like this now:

	+ assets
	+ cms
	+ customhtmlform
	+ dataobject_manager
	+ googlesitemaps
	- mysite
	  - code
		  MySilvercartAddAddressFormExtension.php
		  MySilvercartAddressExtension.php
	  - templates
		  - Layout
			  SilvercartAddAddressForm.ss
	  _config.php
	+ sapphire
	+ silvercart
	+ silvercart_payment_paypal
	+ silvercart_payment_prepayment
	+ themes
	+ uploadify

### Add the extension

So, we created the files, now let's fill them. First, let's touch MySilvercartAddressExtension.php to add the custom fields to the data model. Let's add a mobile phone number and its area code.

What we need to do that is an extension of DataExtension which provides the method extraStatics(). extraStatics() will return an array which consists of additional static values to add to the decorated object. In our case we add the properties “MobilePhone” and “MobilePhoneAreaCode” as text attributes to SilvercartAddess.

Finally, the extension in /mysite/code/MySilvercartAddressExtension.php should look like that:

	:::php
	<?php
	
	/**
	 * Decorates SilvercartAddress.
	 * 
	 * @author Sebastian Diel <sdiel@pixeltricks.de>
	 * @since 10.11.2011
	 */
	class MySilvercartAddressExtension extends DataExtension {
	
		/**
		 * Additional statics for the decorated DataObject. Adds MobilePhone
		 * and MobilePhoneAreaCode to its db attributes.
		 *
		 * @return array
		 * 
		 * @author Sebastian Diel <sdiel@pixeltricks.de>
		 * @since 10.11.2011
		 */
		public function extraStatics() {
			return array(
				'db' => array(
					'MobilePhoneAreaCode' => 'VarChar(10)',
					'MobilePhone' => 'VarChar(50)',
				),
			);
		}
	
	}

Now, let's bring the new fields to the form. To do that, we decorate SilvercartAddAddressForm with an additional extension of DataExtension.

We need to update the form's fields, so we use the updateFormFields() method, which gets the fields by reference.

The extension /mysite/code/MySilvercartAddAddressFormExtension.php needs to look like that:

	:::php
	<?php
	
	/**
	 * Decorates SilvercartAddAddressForm.
	 * 
	 * @author Sebastian Diel <sdiel@pixeltricks.de>
	 * @since 10.11.2011
	 */
	class MySilvercartAddAddressFormExtension extends DataExtension {
	
		/**
		 * This method is called before CustomHtmlForm requires the form fields. You 
		 * can manipulate the default form fields here.
		 * 
		 * @param array &$formFields Form fields to manipulate
		 * 
		 * @return bool
		 * 
		 * @author Sebastian Diel <sdiel@pixeltricks.de>
		 * @since 10.11.2011
		 */
		public function updateFormFields(&$formFields) {
			$formFields['MobilePhoneAreaCode'] = array(
				'type'      => 'TextField',
				'title'     => _t('SilvercartAddress.MOBILEPHONEAREACODE', 'Mobile phone area code'),
				'checkRequirements' => array(
					'isFilledIn' => true
				)
			);
			$formFields['MobilePhone'] = array(
				'type'      => 'TextField',
				'title'     => _t('SilvercartAddress.MOBILEPHONE', 'Mobile phone'),
				'checkRequirements' => array(
					'isFilledIn' => true
				)
			);
		}
	
	}

### Add the new fields to the template

To get the new fields into your templates, add the following markup to your /mysite/templates/Layout/SilvercartAddAddressForm.ss:

	:::php
	<div class="subcolumns">
		<div class="c50l">
			<div class="subcl">
				$CustomHtmlFormFieldByName(MobilePhoneAreaCode)
			</div>
		</div>
		<div class="c50r">
			<div class="subcr">
				$CustomHtmlFormFieldByName(MobilePhone)
			</div>
		</div>
	</div>

### Register the extension

To register the two extensions to the right base objects, open your /mysite/_config.php and add the following lines:

	:::php
	Object::add_extension('SilvercartAddress',          'MySilvercartAddressExtension');
	Object::add_extension('SilvercartAddAddressForm',   'MySilvercartAddAddressFormExtension');

### Flush your cache and build your changes

To get the new stuff working, run a /dev/build/?flush=all on your project:

[http://YOUR_PROJECTS_URL/dev/build/?flush=all]()

Now you have the new address fields available to add and accessible for output and editing.
