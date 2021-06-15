<?php

namespace Jkphl\Micrometa\Ports\Item;

/**
 * Interface to hides all other methods that ItemListInterface inherits from PHP core.
 *
 * Thereby is usable by both the Item for it's properties, as for an ItemList with it's items.
 *
 * Prefer type-hinting against this facade over the ItemList interface.
 *
 * @see ItemListInterface
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
interface CollectionFacade extends \Countable
{
    /**
     * Return an object representation of the item list
     *
     * @return \stdClass Micro information items
     * @api
     */
    public function toObject();

    /**
     * Filter the items by item type(s)
     *
     * @param string[] $types Item types
     *
     * @return ItemInterface[] Items matching the requested types
     * @api
     */
    public function getItems(...$types);

    /**
     * Return the first item, optionally of particular types
     *
     * @param string[] $types Item types
     *
     * @return ItemInterface Item
     * @api
     */
    public function getFirstItem(...$types);
}
