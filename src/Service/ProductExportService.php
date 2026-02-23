<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportService
{
    public function exportCsv(array $products): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($products) {
            $handle = fopen('php://output', 'w+');
            
            fputs($handle, "\xEF\xBB\xBF"); 
            
            fputcsv($handle, [
                'Nom', 
                'Description', 
                'Prix', 
                'Type', 
                'Poids (g)', 
                'Stock'
            ], ';'); 

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->getName(),
                    $product->getDescription(),
                    $product->getPrice(),
                    $product->getType(),    
                    $product->getWeight(),  
                    $product->getStock()    
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export_livres.csv"');

        return $response;
    }
}