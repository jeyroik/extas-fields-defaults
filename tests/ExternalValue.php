<?php
namespace tests;

use extas\interfaces\fields\IField;
use extas\interfaces\IItem;

/**
 * Class ExternalValue
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class ExternalValue
{
    /**
     * @param IField $field
     * @param IItem $item
     * @return string
     */
    public function __invoke(IField $field, IItem $item)
    {
        return 'is ok';
    }
}