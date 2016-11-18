<?php namespace Anon\Support\Contracts;

interface CachedIdentifierInterface
{
    /**
     * Implements a method for retrieving the numeric resource id based on
     * the passed alphanumeric resource identifier.
     *
     * @param  string  $identifier  Alphanumeric resource identifier.
     * @return int     $id          Numeric resource id.
     *
     * @throws ResourceNotFoundException If no resource exists with the provided indentifier.
     */
    public function getIdFromIdentifier($identifier);

}