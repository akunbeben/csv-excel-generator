<?php

namespace App\Traits;

trait Fingerprint
{
    public function fingerprint(): ?string
    {
        if (! $route = request(null)->route()) {
            return null;
        }

        /** @var \Illuminate\Routing\Route $route */
        return sha1(implode('|', array_merge(
            $route->methods(),
            [$route->getDomain(), $route->uri(), request(null)->ip(), now()->timestamp]
        )));
    }
}
