<?php

namespace CustomHtmlForm\Dev;

/**
 * Provides helper methods for CustomHtmlForms for javascript handling.
 *
 * @package CustomHtmlForm
 * @subpackage Dev
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 11.10.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class JavascriptTools {

    /**
     * Creates a Json string from an array recursively
     *
     * @param array $structure the array to be converted to a Json string
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 11.11.2010
     */
    public static function traverseArray($structure) {
        $output = '';

        if (is_array($structure)) {
            foreach ($structure as $structureKey => $structureValue) {
                if ($structureKey !== '') {
                    $output .= $structureKey.': ';
                }

                if (is_array($structureValue)) {

                    $section = self::traverseArray($structureValue, $output);

                    if (!empty($section)) {
                        $section = substr($section, 0, -1);
                    }

                    $output .= "{";
                    $output .= $section;
                    $output .= "},";
                } else {

                    if (is_bool($structureValue)) {
                        $structureValue = $structureValue ? 'true' : 'false';
                    } else if (is_int($structureValue)) {
                    } else {
                        if (strpos($structureValue, '"') === false &&
                            strpos($structureValue, "'") === false) {
                            $structureValue = "'".$structureValue."'";
                        }
                    }

                    $output .= $structureValue.",";
                }
            }
        } else {
            $output = $structure;
        }

        return $output;
    }

    /**
     * accepts a array and returns a string in Json format
     *
     * @param array $structure array of any structure
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 11.11.2010
     */
    public static function createJsonFromStructure($structure) {
        $jsonStr = '';

        if (is_array($structure)) {
            $jsonStr = self::traverseArray($structure);

            if (!empty($jsonStr)) {
                $jsonStr = substr($jsonStr, 0, -1);
                $jsonStr = '{'.$jsonStr.'}';
            }
        } else {
            $jsonStr = $structure;
        }

        return $jsonStr;
    }

    /**
     * Creates a string with JS measures that passes the form fields to the JS Validators
     *
     * @param array $fieldGroups the field groups of the CustomHtmlForm
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 25.10.2010
     */
    public static function generateJsValidatorFields($fieldGroups) {
        $fieldStr = '';

        foreach ($fieldGroups as $groupName => $groupFields) {
            foreach ($groupFields as $fieldName => $fieldProperties) {
                $checkRequirementStr    = '';
                $eventStr               = '';
                $configurationStr       = '';

                // ------------------------------------------------------------
                // create JS requirements
                // ------------------------------------------------------------
                if (isset($fieldProperties['checkRequirements'])) {
                    foreach ($fieldProperties['checkRequirements'] as $requirement => $definition) {
                        $checkRequirementStr .= self::generateJsValidatorRequirementString($requirement, $definition);
                    }
                }
                if (!empty($checkRequirementStr)) {
                    $checkRequirementStr = substr($checkRequirementStr, 0, strlen($checkRequirementStr) - 1);
                }

                // ------------------------------------------------------------
                // create JS event
                // ------------------------------------------------------------
                if (isset($fieldProperties['jsEvents'])) {
                    foreach ($fieldProperties['jsEvents'] as $event => $definition) {
                        $eventStr .= self::generateJsValidatorEventString($event, $definition);
                    }
                }
                if (!empty($eventStr)) {
                    $eventStr = substr($eventStr, 0, strlen($eventStr) - 1);
                }

                // ------------------------------------------------------------
                // create configuration section
                // ------------------------------------------------------------
                if (isset($fieldProperties['configuration'])) {
                    foreach ($fieldProperties['configuration'] as $configuration => $definition) {
                        $configurationStr .= self::generateJsValidatorRequirementString($configuration, $definition);
                    }
                }
                if (!empty($configurationStr)) {
                    $configurationStr = substr($configurationStr, 0, strlen($configurationStr) - 1);
                }

                // ------------------------------------------------------------
                // add additional attributes
                // ------------------------------------------------------------
                if (isset($fieldProperties['title'])) {
                    $titleField = 'title: "'.str_replace('"', '\"', $fieldProperties['title']).'",';
                } else {
                    $titleField = '';
                }

                $fieldStr .= sprintf(
                    "'%s': {type: \"%s\", %s checkRequirements: {%s}, events: {%s}, configuration: {%s}},",
                    $fieldName,
                    $fieldProperties['type'],
                    $titleField,
                    $checkRequirementStr,
                    $eventStr,
                    $configurationStr
                );
            }
        }

        if (!empty($fieldStr)) {
            $fieldStr = substr($fieldStr, 0, strlen($fieldStr) - 1);
        }

        return $fieldStr;
    }

    /**
     * Returns a string of JS code created from the passed parameters
     *
     * @param string $event      events name
     * @param mixed  $definition the definition
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.11.2010
     */
    public static function generateJsValidatorEventString($event, $definition) {
        $eventStr               = '';
        $eventFieldMappingsStr  = '';

        if ($event == 'setValueDependantOn') {

            $eventReferenceField = $definition[0];

            foreach ($definition[1] as $referenceFieldValue => $mapping) {

                $mappingStr = '';

                foreach ($mapping as $key => $value) {
                    if (is_bool($value)) {
                        $value = $value ? 'true' : 'false';
                    } else if (is_int($value)) {
                    } else {
                        $value = "'".$value."'";
                    }
                    if (!empty($key)) {
                        $mappingStr .= $key.': '.$value.',';
                    } else {
                        $mappingStr .= 'CustomHtmlFormEmptyValue: '.$value.',';
                    }
                }
                if (!empty($mappingStr)) {
                    $mappingStr = substr($mappingStr, 0, -1);
                }

                $eventFieldMappingsStr .= $referenceFieldValue.': {';
                $eventFieldMappingsStr .= $mappingStr;
                $eventFieldMappingsStr .= '},';
            }
            if (!empty($eventFieldMappingsStr)) {
                $eventFieldMappingsStr = substr($eventFieldMappingsStr, 0, -1);
            }

            $eventStr .= $event.': {';
            $eventStr .= $eventReferenceField.': {';
            $eventStr .= $eventFieldMappingsStr;
            $eventStr .= '}';
            $eventStr .= '},';
        } else {
            $eventStr .= $event.': ';
            $eventStr .= self::createJsonFromStructure($definition);
            $eventStr .= ',';
        }

        return $eventStr;
    }

    /**
     * Returns a string of JS code created from the passed parameters
     *
     * @param string $requirement name of the requirement
     * @param mixed  $definition  the definition
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 10.11.2010
     *
     */
    public static function generateJsValidatorRequirementString($requirement, $definition) {

        $checkRequirementStr = '';

        if (is_array($definition)) {
            $subCheckRequirementStr = '';
            foreach ($definition as $subRequirement => $subDefinition) {
                if (is_bool($subDefinition)) {
                    $subDefinitionStr = $subDefinition ? 'true' : 'false';
                } else if (is_int($subDefinition)) {
                    $subDefinitionStr = $subDefinition;
                } else {
                    $subDefinitionStr = "'".$subDefinition."'";
                }

                $subCheckRequirementStr .= $subRequirement.": ".$subDefinitionStr.",";
            }

            if (!empty($subCheckRequirementStr)) {
                $subCheckRequirementStr = substr($subCheckRequirementStr, 0, strlen($subCheckRequirementStr) - 1);

                $checkRequirementStr .= $requirement.': {';
                $checkRequirementStr .= $subCheckRequirementStr;
                $checkRequirementStr .= '},\n';
            }
        } else {
            if (is_bool($definition)) {
                $definitionStr = $definition ? 'true' : 'false';
            } else if (is_int($definition)) {
                $definitionStr = $definition;
            } else {
                $definitionStr = "'".$definition."'";
            }

            $checkRequirementStr .= $requirement.": ".$definitionStr.",\n";
        }

        if (!empty($checkRequirementStr)) {
            $checkRequirementStr = substr($checkRequirementStr, 0, -1);
        }

        return $checkRequirementStr;
    }
}