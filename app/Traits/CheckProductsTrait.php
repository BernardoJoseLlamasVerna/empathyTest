<?php

namespace App\Traits;

use Carbon\Carbon;

trait CheckProductsTrait
{
    public function getProductsChecked() {
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
            // check on_sale value is always lower than regular price:

            // check variations: if same color-material exported, discard old versions and take more recent:
            $colorMaterialCombination = [];
            foreach ($product['variations'] as $variation) {
                $key = $variation['material'].$variation['color'];
                if (!array_key_exists($key, $colorMaterialCombination)
                    || $this->isMoreRecent($variation, $colorMaterialCombination[$key]))
                {

                    $variation['updatedAt'] = $this->reformatDate($variation['updatedAt'])->toISOString();
                    $colorMaterialCombination[] = $variation;
                }
            }

            $product['variations'] = $colorMaterialCombination;
            // check variations: if same color-material exported, discard old versions and take more recent:

            $product['updatedAt'] = $this->reformatDate($product['updatedAt'])->toISOString();
            $productsAfterChecked['products'][] = $product;
        }

        return $productsAfterChecked;
    }

    /**
     * Check which product version is more recent.
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
     * Convert updatedAt to the following format "2020-09-14T15:09:30.000000Z".
     * @param $date
     * @return float|string
     */
    private function reformatDate($date) {
        return Carbon::parse(preg_replace('/\s+\(.*\)$/', '', $date));
    }
}
