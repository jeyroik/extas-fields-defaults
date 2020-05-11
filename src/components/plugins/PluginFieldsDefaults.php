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
                $item[$field->getName()] = $this->getValue($field, $item);
            }
        }
    }

    /**
     * @param IField $field
     * @param IItem $item
     * @return mixed
     */
    protected function getValue(IField $field, IItem $item)
    {
        $value = $field->getValue();

        if (is_string($value) && class_exists($value)) {
            $class = new $value();
            return $class($field, $item);
        }

        return $value;
    }
}
