<?php
class MST_Pdp_Block_Adminhtml_Widget_Grid_Column_Filter_Select
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    /**
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select::_getOptions()
     */
    protected function _getOptions()
    {
        $emptyOption = array('value' => null, 'label' => '');

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        // Options have already been setup in a way that is "label" / "value" format. Don't mess with it any further
        if (isset($colOptions[0]) && isset($colOptions[0]['label'])) {
            return $colOptions;
        }

        if (!empty($colOptions) && is_array($colOptions) ) {
            $options = array($emptyOption);
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
            return $options;
        }
        return array();
    }
}