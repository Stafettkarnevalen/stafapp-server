<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 27/06/2018
 * Time: 15.25
 */

namespace App\Command;

use function GuzzleHttp\Promise\is_fulfilled;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportOPLACommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:import-opla')

            // the short description shown while running "php bin/console list"
            ->setDescription('Imports schools from opla csv file')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to import schools from a csv file')
            // required params
            ->setDefinition(new InputDefinition([
                new InputOption('input', 'i', InputOption::VALUE_REQUIRED, "The input file containg comma separated school data.")
            ]))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Import OPLA');

        /** @var ManagerRegistry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $file = $input->getOption('input');

        if (!$file) {
            //$io->error('Required parameter input is missing.');
            //return;
            $io->note('Import needs to know the datasource of the import. The datasource needs to be a comma separated file containing the schools.');
            $file = $io->ask('Please enter input file path:');
        }
        if (!is_file($file)) {
            $io->error('File not found: ' . $file);
            return;
        }
    }
}