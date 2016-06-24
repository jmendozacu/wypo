<?php
class MST_Pdp_Block_Adminhtml_Widget_Grid_Column_Renderer_Options
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {

            // Call our new function if necessary.
            if (   isset($options[0]) && isset($options[0]['value'])
                || isset($options[1]) && isset($options[1]['value'])
            ) {
                $options = $this->_getFlatOptions($options);
            }

            $value = $row->getData($this->getColumn()->getIndex());

            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                return $this->escapeHtml($options[$value]);
            } elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }

    /**
     * Our new function that will turn a set of options with option groups, to a flat array of options.
     *
     * @param array $grouped_options
     */
    protected function _getFlatOptions($grouped_options)
    {
        $options = array();

        foreach ($grouped_options as $option) {
            if ('' == $option['value'] || null === $option['value']) {
                continue;
            }

            if (is_string($option['value'])) {
                $options[$option['value']] = $option['label'];
            } elseif (is_array($option['value'])) {
                foreach ($option['value'] as $opt) {
                    $options[$opt['value']] = $opt['label'];
                }
            }
        }

        return $options;
    }
}