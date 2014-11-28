<?php
/**
 * Copyright 2013 pixeltricks GmbH
 *
 * This file is part of CustomHtmlForms.
 *
 * CustomHtmlForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * CustomHtmlForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with CustomHtmlForms.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package CustomHtmlForm
 */

/**
 * Provides multipage functionality for CustomHtmlForms
 *
 * @package CustomHtmlForm
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2013 pixeltricks GmbH
 * @since 2013-02-14
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class CustomHtmlFormStep extends CustomHtmlForm {

    /**
     * returns the form step's title
     *
     * @return string
     */
    public function getStepTitle() {
        if (isset($this->preferences['stepTitle'])) {
            $title = $this->preferences['stepTitle'];
        } else {
            $title = $this->basePreferences['stepTitle'];
        }

        return $title;
    }

    /**
     * is the form visible?
     *
     * @return boolean
     */
    public function getStepIsVisible() {
        if (isset($this->preferences['stepIsVisible'])) {
            $isVisible = $this->preferences['stepIsVisible'];
        } else {
            $isVisible = $this->basePreferences['stepIsVisible'];
        }

        return $isVisible;
    }

    /**
     * Is the defined step conditional?
     *
     * @return bool
     */
    public function getIsConditionalStep() {
        if (isset($this->preferences['isConditionalStep'])) {
            $isConditionalStep = $this->preferences['isConditionalStep'];
        } else {
            $isConditionalStep = $this->basePreferences['isConditionalStep'];
        }

        return $isConditionalStep;
    }

    /**
     * is the defined step the recent step?
     *
     * @return bool
     */
    public function getIsCurrentStep() {
        $isCurrentStep = false;

        if ($this->controller->getCurrentStep() == $this->getStepNr()) {
            $isCurrentStep = true;
        }

        return $isCurrentStep;
    }

    /**
     * is the step completed already
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.12.2010
     */
    public function isStepCompleted() {
        $completed = false;
        $stepIdx   = $this->getStepNr();

        if (in_array($stepIdx, $this->controller->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * Is the previous step completed?
     *
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 23.12.2010
     */
    public function isPreviousStepCompleted() {
        $completed = false;
        $stepIdx   = $this->getStepNr() - 1;

        if (in_array($stepIdx, $this->controller->getCompletedSteps())) {
            $completed = true;
        }

        return $completed;
    }

    /**
     * Returns true if this is the last step.
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 06.04.2011
     */
    public function isLastStep() {
        $step = $this->controller->getStepList()->find('stepNr', $this->getStepNr());

        if ($step &&
            $step->isLastVisibleStep) {

            return true;
        }
        return false;
    }

    /**
     * Returns the output of a form that was initialised by a
     * CustomHtmlFormStepPage object.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 06.04.2011
     */
    public function CustomHtmlFormInitOutput() {
        return $this->controller->getInitOutput();
    }

    /**
     * returns the step number of this form
     *
     * @return int
     */
    public function getStepNr() {
        $stepList = $this->controller->getStepList();
        $stepNr   = 1;

        foreach ($stepList as $step) {
            if ($step->step->class == $this->class) {
                $stepNr = $step->stepNr;
                break;
            }
        }

        return $stepNr;
    }

    /**
     * returns the visible step number of this form
     *
     * @return int
     */
    public function getVisibleStepNr() {
        $stepList = $this->controller->getStepList();
        $stepNr   = 1;

        foreach ($stepList as $step) {
            if ($step->step->class == $this->class) {
                $stepNr = $step->visibleStepNr;
                break;
            }
        }

        return $stepNr;
    }

    /**
     * Should the step navigation be shown?
     *
     * @return bool
     */
    protected function getShowCustomHtmlFormStepNavigation() {
        return $this->getPreference('ShowCustomHtmlFormStepNavigation');
    }
}