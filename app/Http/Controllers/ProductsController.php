<?php

namespace App\Http\Controllers;

use App\Traits\CheckProductsTrait;

class ProductsController extends Controller
{
    use CheckProductsTrait;

    /**
     * Function that gets all products from http://0.0.0.0:7000/feed/products endpoint
     * and returns them after being checked.
     *
     * Two tests to each product are done:
     * - fix regular and on_sale prices so regular price always has to be lower than on_sale price.
     * - check variations of each product: if there is more than one combination of color-material, old versions are discarded.
     */
    public function getProducts() {
        return \GuzzleHttp\json_encode($this->getProductsChecked());
    }
}
