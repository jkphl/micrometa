<?php

namespace Jkphl\Micrometa\Ports\Item;

use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

/**
 * Abstract getter functionality that is applicable for implementations of ItemCollectionFacade.
 *
 * The implementation remains responsible for implementing the collection methods itself.
 *
 * Thereby is usable by both the Item for it's properties, as for an ItemList with it's items.
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
abstract class Collection implements CollectionFacade
{
    /**
     * Generic item getter
     *
     * @param string $type     Item type
     * @param array $arguments Arguments
     *
     * @return ItemInterface Item
     * @throws InvalidArgumentException If the item index is invalid
     * @api
     */
    public function __call($type, $arguments)
    {
        $index = 0;
        if (count($arguments)) {
            // If the item index is invalid
            if (!is_int($arguments[0]) || ($arguments[0] < 0)) {
                throw new InvalidArgumentException(
                    sprintf(InvalidArgumentException::INVALID_ITEM_INDEX_STR, $arguments[0]),
                    InvalidArgumentException::INVALID_ITEM_INDEX
                );
            }

            $index = $arguments[0];
        }

        // Return the item by type and index
        return $this->getItemByTypeAndIndex($type, $index);
    }

    /**
     * Return an item by type and index
     *
     * @param string $type Item type
     * @param int $index   Item index
     *
     * @return ItemInterface Item
     * @throws OutOfBoundsException If the item index is out of bounds
     */
    private function getItemByTypeAndIndex($type, $index)
    {
        $typeItems = $this->getItems($type);

        // If the item index is out of bounds
        if (count($typeItems) <= $index) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_ITEM_INDEX_STR, $index),
                OutOfBoundsException::INVALID_ITEM_INDEX
            );
        }

        return $typeItems[$index];
    }
}
