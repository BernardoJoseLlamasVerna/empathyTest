<?php

namespace App\Http\Controllers;

use App\Traits\CheckProductsTrait;

class AdsController extends Controller
{
    use CheckProductsTrait;

    /**
     * Function that returns products advertisements
     * @return string
     */
    public function getAds()
    {
        $products = $this->getProductsChecked();
        $adsArray['advertisements'] = [];

        foreach ($products['products'] as $product) {
            // check product stock and if is enabled/disabled
            $results = $this->checkProductStock($product['availability']['stock']);
            $message = $results[0];
            $enabled = $results[1];
            //images array;
            $images = $this->getAllImagesUrl($product['image']);
            // calculate discount:
            $discount = $this->discountPercentage($product['price']);
            // get all product colors and materials:
            $colorAndMaterials = $this->getAllColorsAndMaterials($product['variations']);
            // final advertisement array
            $advertiseInfo = $this->finalAdInfo($product, $images, $discount, $message, $colorAndMaterials);

            if($enabled) {
                $adsArray['advertisements']['enabled'][] = $advertiseInfo;
            } else {
                $adsArray['advertisements']['disabled'][] = $advertiseInfo;
            }
        }

        return \GuzzleHttp\json_encode($adsArray);
    }

    /**
     * Checks product stock and returns a message and boolean (enabled/disabled)
     * @param $stock
     * @return array
     */
    private function checkProductStock($stock) {
        $message = '';
        $enabled = true;

        if ($stock <= 0) {
            $message = 'Out of Stock!';
            $enabled = false;
        }
        if ($stock > 0 && $stock < 10) {
            $message = 'Last Units!';
        }
        if ($stock >= 10 && $stock < 50) {
            $message = 'Bestseller!';
        }
        if ($stock > 80) {
            $message = 'New Product!';
        }

        return [$message, $enabled];
    }

    /**
     * Function that returns all images url associated to a product.
     * @param $images
     * @return array
     */
    private function getAllImagesUrl($images) {
        $imagesFinalArray = [];

        foreach ($images as $image) {
            if($this->checkIfIsInArray($image['url'], $imagesFinalArray)) {
                $imagesFinalArray[] = $image['url'];
            }
        }

        return $imagesFinalArray;
    }

    /**
     * Function that returns product discount percentage from regular and on_sale prices.
     * @param $price
     * @return string
     */
    private function discountPercentage($price) {
        $regularPrice = $price['regular'];
        $onSalePrice = $price['on_sale'];

        return round(($regularPrice - $onSalePrice)*100/$regularPrice, 2).'%';
    }

    /**
     * Function that returns two arrays with all colors and materials associated to a product.
     * @param $variations
     * @return array[]
     */
    private function getAllColorsAndMaterials($variations) {
        $colors = [];
        $materials = [];

        foreach ($variations as $variation) {
            if($this->checkIfIsInArray($variation['color'], $colors)) {
                $colors[] = $variation['color'];
            }

            if($this->checkIfIsInArray($variation['material'], $materials)) {
                $materials[] = $variation['material'];
            }
        }

        return [$colors, $materials];
    }

    /** Check if an element is in array. Return true if not.
     * @param $element
     * @param $array
     * @return bool
     */
    private function checkIfIsInArray($element, $array) {
        return !in_array($element, $array);
    }

    /**
     * Function that returns final product advertisement info.
     * @param $product
     * @param $images
     * @param $discount
     * @param $message
     * @param $colorAndMaterials
     * @return array
     */
    private function finalAdInfo($product, $images, $discount, $message, $colorAndMaterials) {
        $finalAdInfo = [];

        $finalAdInfo['id'] = $product['id'];
        $finalAdInfo['name'] = $product['name'];
        $finalAdInfo['link'] = $product['link'];
        $finalAdInfo['images'] = $images;
        $finalAdInfo['discount'] = $discount;
        $finalAdInfo['message'] = $message;
        $finalAdInfo['colors'] = $colorAndMaterials[0];
        $finalAdInfo['materials'] = $colorAndMaterials[1];

        return $finalAdInfo;
    }
}
