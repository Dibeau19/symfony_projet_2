<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'app:add-client', description: 'Ajoute un nouveau client via la console')]
class AddClientCommand extends Command
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Ajout d\'un nouveau client');

        $nom = $io->ask('Nom du client');
        $prenom = $io->ask('Prénom du client');
        $email = $io->ask('Email');
        $telephone = $io->ask('Numéro de téléphone');
        $adresse = $io->ask('Adresse');

        $client = new Client();
        $client->setFirstname($prenom);
        $client->setLastname($nom);
        $client->setEmail($email);
        $client->setPhonenumber($telephone);
        $client->setAddress($adresse);

        $errors = $this->validator->validate($client);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getMessage());
            }
            return Command::FAILURE;
        }

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $io->success('Le client a été ajouté avec succès !');

        return Command::SUCCESS;
    }
}