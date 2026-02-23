<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:import-products', description: 'Importe des produits depuis un fichier CSV dans public/')]
class ImportProductsCommand extends Command
{
    private $entityManager;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $publicPath = $this->params->get('kernel.project_dir') . '/public/';
        $filePath = $publicPath . 'produits.csv';

        if (!file_exists($filePath)) {
            $io->error(sprintf('Le fichier n\'existe pas au chemin : %s', $filePath));
            return Command::FAILURE;
        }

        $io->title('Importation des produits...');

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $product = new Product();
                $product->setType($data[0]);
                $product->setName($data[1]);
                $product->setDescription($data[2]);
                $product->setWeight($data[3]);
                $product->setStock($data[4]);
                $product->setPrice((float) $data[5]); 

                $this->entityManager->persist($product);
                $count++;
            }
            
            fclose($handle);
            
            $this->entityManager->flush();
            
            $io->success(sprintf('%d produits ont été importés avec succès !', $count));
        } else {
            $io->error('Impossible d\'ouvrir le fichier.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}