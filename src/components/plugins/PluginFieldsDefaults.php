<?php
namespace extas\components\plugins;

use extas\interfaces\fields\IField;
use extas\interfaces\IItem;
use extas\interfaces\stages\IStageItemInit;

/**
 * Class PluginFieldsDefaults
 *
 * @method fieldRepository()
 *
 * @package extas\components\plugins
 * @author jeyroik@gmail.com
 */
class PluginFieldsDefaults extends Plugin implements IStageItemInit
{
    /**
     * @param IItem $item
     */
    public function __invoke(IItem &$item): void
    {
        /**
         * @var IField[] $fields
         */
        $subject = $item->__subject();
        $fields = $this->fieldRepository()->all([
            IField::FIELD__PARAMETERS . '.subject.value' => $subject
        ]);

        foreach ($fields as $field) {
            if (!$item->has($field->getName())) {
                $item[$field->getName()] = $this->getValue($field);
            }
        }
    }

    /**
     * @param IField $field
     * @return mixed
     */
    protected function getValue(IField $field)
    {
        $value = $field->getValue();
        if (is_callable($value)) {
            return $value();
        }

        return $value;
    }
}
