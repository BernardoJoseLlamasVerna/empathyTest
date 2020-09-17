<?php

namespace App\Traits;

use Carbon\Carbon;

trait CheckProductsTrait
{
    /**
     * Function that gets all products from http://0.0.0.0:7000/feed/products endpoint.
     *
     * Two tests to each product are done:
     * - fix regular and on_sale prices so regular price always has to be greater than on_sale price.
     * - check variations of each product: if a color-material combination is repeated, old versions are discarded.
     *
     * @return array
     */
    public function getProductsChecked() {
        // test /feed/products endpoint
        if (!file_get_contents("http://0.0.0.0:7000/feed/products")) {
            $error = error_get_last();
            echo "HTTP request failed. Error was: " . $error['message'];
        }
        // get products from url http://0.0.0.0:7000/feed/products:
        $products = json_decode(file_get_contents("http://0.0.0.0:7000/feed/products"), true);

        $productsAfterChecked = [];
        foreach ($products['products'] as $product) {
            // check on_sale value is always lower than regular price:
            $regularPrice = $product['price']['regular'];
            $onSalePrice = $product['price']['on_sale'];
            if($regularPrice < $onSalePrice) {
                $product['price']['regular'] = $onSalePrice;
                $product['price']['on_sale'] = $regularPrice;
            }

            // check variations: if same color-material combination, discard old versions and take the recent one:
            $colorMaterialCombination = [];
            $product['variations'] = $this->checkColorMaterialCombinations(
                $product['variations'],
                $colorMaterialCombination
            );

            $product['updatedAt'] = $this->reformatDate($product['updatedAt']);
            $productsAfterChecked['products'][] = $product;
        }

        return $productsAfterChecked;
    }

    /**
     * Checks combinations of color-material associated to a product.
     *  - if color-material combination doesn't exist it's included on final $colorMaterialCombination array.
     *  - if color-material combination exists, more recent is included on final $colorMaterialCombination array.
     * @param $variations
     * @param $colorMaterialCombination
     * @return mixed
     */
    private function checkColorMaterialCombinations($variations, $colorMaterialCombination) {
        foreach ($variations as $variation) {
            $key = $variation['material'].$variation['color'];
            if (!array_key_exists($key, $colorMaterialCombination)
                || $this->isMoreRecent($variation, $colorMaterialCombination[$key]))
            {
                $variation['updatedAt'] = $this->reformatDate($variation['updatedAt']);
                $colorMaterialCombination[] = $variation;
            }
        }

        return $colorMaterialCombination;
    }

    /**
     * Checks which product's color-material combination version is more recent.
     *
     * @param $newVariation
     * @param $oldVariation
     * @return bool
     */
    private function isMoreRecent($newVariation, $oldVariation) {
        $newVariationFormat = $this->reformatDate($newVariation);
        $oldVariationFormat = $this->reformatDate($oldVariation);

        if ($newVariationFormat->greaterThan($oldVariationFormat))  {
            return true;
        }
        return false;
    }

    /**
     * Converts updatedAt date to the following format i.e: "2020-09-14T15:09:30.000000Z".
     * @param $date
     * @return float|string
     */
    private function reformatDate($date) {
        return Carbon::parse(preg_replace('/\s+\(.*\)$/', '', $date));
    }
}
