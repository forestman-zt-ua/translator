<?php

namespace Cloneble\Translator\Plugin;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class ApiKeyValidator
{
    /**
     * Before save plugin to validate API key.
     *
     * @param Value $subject
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave(Value $subject)
    {
        $fieldId = $subject->getPath();
        if ($fieldId === 'translator/general/api_key') {
            $value = $subject->getValue();

            if (!$this->isValidApiKey($value)) {
                throw new LocalizedException(__('Please enter a valid API key.'));
            }
        }
    }

    private function isValidApiKey($value)
    {
        return true;
    }
}
