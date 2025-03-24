<?php

namespace App\Traits;

trait HelperTrait
{
    protected function arraysAreEqual(array $arrayOld, array $arrayNew): bool|array
    {
        array_walk_recursive($arrayOld, function (&$item) {
            $item = (int)$item;
        });
        array_walk_recursive($arrayNew, function (&$item) {
            $item = (int)$item;
        });

        // Ordenar os arrays pelo valor do `product_id` para comparação
        usort($arrayOld, function ($a, $b) {
            return $a['product_id'] <=> $b['product_id'];
        });
        usort($arrayNew, function ($a, $b) {
            return $a['product_id'] <=> $b['product_id'];
        });

        // Encontrar diferenças (itens em array1 que não estão em array2)
        $differences1 = array_udiff($arrayOld, $arrayNew, function ($a, $b) {
            return strcmp(json_encode($a), json_encode($b));
        });

        // Encontrar diferenças (itens em array2 que não estão em array1)
        $differences2 = array_udiff($arrayNew, $arrayOld, function ($a, $b) {
            return strcmp(json_encode($a), json_encode($b));
        });

        return [
            'delete' => $differences1,
            'store' => $differences2,
        ];
    }
}
