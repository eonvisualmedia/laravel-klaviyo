<?php

namespace EonVisualMedia\LaravelKlaviyo\Contracts;

interface ViewedProduct
{
    public function getViewedProductProperties(): array;

    public function getViewedItemProperties(): array;
}
