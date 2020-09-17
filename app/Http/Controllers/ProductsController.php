<?php

namespace App\Http\Controllers;

use App\Traits\CheckProductsTrait;

class ProductsController extends Controller
{
    use CheckProductsTrait;

    /**
     * Function that gets all products from CheckProductsTrait's getProductsChecked() function.
     */
    public function getProducts() {
        return \GuzzleHttp\json_encode($this->getProductsChecked());
    }
}
