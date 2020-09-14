<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Function that gets all products from http://0.0.0.0:7000/feed/products endpoint
     * and returns them after being checked.
     *
     * Two tests to each product are done:
     * - fix regular and on_sale prices so regular price always has to be lower than on_sale price.
     * - check variations of each product: if there is more than one combination of color-material, old versions are discarded.
     */
    public function getProducts() {
        // get products from url http://0.0.0.0:7000/feed/products:
        /*$products = json_decode(file_get_contents("http://0.0.0.0:7000/feed/products"), true);

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
                    // if doesnt exist on our array solution or is the newest version
                    $colorMaterialCombination[] = $variation;
                }
            }

            $product['variations'] = $colorMaterialCombination;
            $product['updatedAt'] = $this->reformatDate($product['updatedAt'])->toISOString();

            $productsAfterChecked['products'][] = $product;
        }*/

        return \GuzzleHttp\json_encode($this->getProductsChecked());
    }

    public function getProductsChecked() {
    // private function getProductsChecked() {
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
                    // if doesnt exist on our array solution or is the newest version
                    $colorMaterialCombination[] = $variation;
                }
            }

            $product['variations'] = $colorMaterialCombination;
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
            // dd('holaaa');
            return true;
        }
        return false;
    }

    /**
     * Convert data given on updatedAt product field and returns it as milliseconds value.
     * @param $date
     * @return float|string
     */
    private function reformatDate($date) {
        $month = substr($date, 4, 3);
        $month = intval($this->getMonthNumber($month));
        $day = intval(substr($date, 8, 2));
        $year = intval(substr($date, 11, 4));
        $hour = substr($date, 16, 2);
        $minutes = substr($date, 19, 2);
        $seconds = substr($date, 22, 2);

        return Carbon::create($year, $month, $day, $hour, $minutes, $seconds);
    }

    /**
     * Returns month value.
     *
     * @param $month
     * @return false|int|string
     */
    private function getMonthNumber($month) {
        $monthKeyValues = [
            '1' => 'Jan',
            '2' => 'Feb',
            '3' => 'Mar',
            '4' => 'Apr',
            '5' => 'May',
            '6' => 'Jun',
            '7' => 'Jul',
            '8' => 'Ago',
            '9' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        return array_search($month, $monthKeyValues);
    }
}
