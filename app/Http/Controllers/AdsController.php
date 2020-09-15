<?php

namespace App\Http\Controllers;

use App\Traits\CheckProductsTrait;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    use CheckProductsTrait;

    public function getAds()
    {
        // dd("hellooo");

        $products = $this->getProductsChecked();

        //$adsArray = [];
        $adsArray['advertisements'] = [];
        /*$adsArray['advertisements']['enabled'] = [];
        $adsArray['advertisements']['disabled'] = [];
        dd($adsArray);*/

        foreach ($products['products'] as $product) {
            //dd($product);

            // check product stock and if is enabled/disabled
            $results = $this->checkProductStock($product['availability']['stock']);
            $message = $results[0];
            $enabled = $results[1];
            // check product stock and if is enabled/disabled

            // calculate discounts:
            $discount = $this->discountPercentage($product['price']);
            // calculate discounts:

            // get all product colors and materials:
            $colorAndMaterials = $this->getAllColorsAndMaterials($product['variations']);

            $colors = $colorAndMaterials[0];
            $materials = $colorAndMaterials[1];
            // get all product colors and materials:

            // final advertisement array
            $advertiseInfo = $this->finalAdInfo($product, $discount, $message, $colorAndMaterials);

            if($enabled) {
                $adsArray['advertisements']['enabled'][] = $advertiseInfo;
            } else {
                $adsArray['advertisements']['disabled'][] = $advertiseInfo;
            }
            // final advertisement array

        }

        dd($adsArray);
    }

    private function checkProductStock($stock) {
        $message = '';
        $enabled = true;

        if ($stock <= 0) {
            $message = 'Out of Stock!';
            $enabled = false;
        }

        if ($stock >= 0 && $stock < 10) {
            $message = 'Last Units!';
        }

        if ($stock >= 10 && $stock < 50) {
            $message = 'Bestseller!';
        }

        if ($stock >= 50 && $stock < 80) {
            $message = 'New Product!';
        }

        return [$message, $enabled];
    }

    private function discountPercentage($price) {
        $regularPrice = $price['regular'];
        $onSalePrice = $price['on_sale'];

        return round(($regularPrice - $onSalePrice)*100/$regularPrice, 2).'%';
    }

    private function getAllColorsAndMaterials($variations) {
        $colors = [];
        $materials = [];

        foreach ($variations as $variation) {
            $colors[] = $variation['color'];
            $materials[] = $variation['material'];
        }

        //dd($materials);
        return [$colors, $materials];
    }

    private function finalAdInfo($product, $discount, $message, $colorAndMaterials) {
        $finalAdInfo = [];

        $finalAdInfo['id'] = $product['id'];
        $finalAdInfo['name'] = $product['name'];
        $finalAdInfo['link'] = $product['link'];
        $finalAdInfo['images'] = $product['image'];
        $finalAdInfo['discount'] = $discount;
        $finalAdInfo['message'] = $message;
        $finalAdInfo['colors'] = $colorAndMaterials[0];
        $finalAdInfo['materials'] = $colorAndMaterials[1];

        return $finalAdInfo;
    }
}
