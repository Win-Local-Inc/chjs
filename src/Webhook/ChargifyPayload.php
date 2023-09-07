<?php

namespace WinLocalInc\Chjs\Webhook;

class ChargifyPayload
{

    public object $payload;

    /**
     * ChargifyPayload constructor.
     */
    public
    function __construct ( array $payload )
    {
        $this->payload = $this->cast( $payload );
    }

    protected
    function cast ( $payload )
    {
        if ( !is_array( $payload ) )
        {
            return $payload;
        }

        foreach ( $payload as &$item )
        {
            $item = $this->cast( $item );
        }

        return (object)$payload;
    }
}
