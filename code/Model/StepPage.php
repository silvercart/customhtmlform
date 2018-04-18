<?php

namespace CustomHtmlForm\Model;

use Page;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\CMS\Model\SiteTree;

/**
 * page type that must be instanciated in the backend for a multi step form
 *
 * A base name (field "basename" for the form object and the template files of
 * the form must be defined
 *
 * @package CustomHtmlForm
 * @subpackage Model
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class StepPage extends Page {

    /**
     * Definiert die Datenfelder.
     *
     * @var array
     */
    private static $db = array(
        'basename'       => 'Varchar(255)',
        'showCancelLink' => 'Boolean(1)',
        'cancelPageID'   => 'Varchar(255)'
    );

    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'CustomHtmlFormStepPage';
    
    /**
     * The defined value will be added to the step number to show in frontend
     * checkout navigation.
     *
     * @var int
     */
    public static $add_to_visible_step_nr = 0;
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 20.10.2017
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),
                array(
                    'BaseName'     => _t(StepPage::class . '.BASE_NAME', 'base name for form object and template files: '),
                    'ShowCancel'   => _t(StepPage::class . '.SHOW_CANCEL', 'show cancel link'),
                    'CancelTarget' => _t(StepPage::class . '.CANCEL_TARGET', 'To which page should the cancel link direct: '),
                )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * defines the CMS interface for $this
     * 
     * @return FieldList
     */
    public function getCMSFields() {

        $basenameField       = new TextField('basename', $this->fieldLabel('BaseName'));
        $showCancelLinkField = new CheckboxField('showCancelLink', $this->fieldLabel('ShowCancel'));
        $cancelLinkField     = new TreeDropdownField('cancelPageID', $this->fieldLabel('CancelTarget'), SiteTree::class);

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.MultistepConfiguration', $basenameField);
        $fields->addFieldToTab('Root.MultistepConfiguration', $showCancelLinkField);
        $fields->addFieldToTab('Root.MultistepConfiguration', $cancelLinkField);

        return $fields;
    }
}