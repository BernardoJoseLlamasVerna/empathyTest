<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdsController extends ProductsController
{
    public function getAds()
    {
        // dd("hellooo");

        $products = $this->getProductsChecked();

        $adsArray = [];
        $adsArray['advertisements'] = [];
        $adsArray['advertisements']['enabled'] = [];
        $adsArray['advertisements']['disabled'] = [];
        // dd($adsArray);

        foreach ($products['products'] as $product) {
            //dd($product['availability']['stock']);

            $adsArray['advertisements'] = $this->checkProductStock($product, $product['availability']['stock']);
            dd($adsArray['advertisements']);
        }

        dd($adsArray);
    }

    private function checkProductStock($product, $stock) {
        $message = '';

        if ($stock <= 0) {
            $message = 'Out of Stock!';
            $adsArray['advertisements']['disabled'] = $product;
        }

        if ($stock >= 0 && $stock < 10) {
            $message = 'Last Units!';
            $adsArray['advertisements']['enabled'] = $product;
        }

        if ($stock >= 10 && $stock < 50) {
            $message = 'Bestseller!';
            $adsArray['advertisements']['enabled'] = $product;
        }

        if ($stock >= 50 && $stock < 80) {
            $message = 'New Product!';
            $adsArray['advertisements']['enabled'] = $product;
        }

        return [$message, $adsArray];
    }
}
