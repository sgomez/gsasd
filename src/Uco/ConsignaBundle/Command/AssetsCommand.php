<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 17/10/13
 * Time: 13:29
 */

namespace Uco\ConsignaBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class AssetsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('bootstrap:install')
            ->setDescription('Instala los assets de bootstrap')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = $this->getContainer()->get('kernel')->getRootDir().'/..';
        $fs = new Filesystem();
        try {
            $fs->mirror($base . '/vendor/twitter/bootstrap/dist', $base.'/src/Uco/ConsignaBundle/Resources/public');
            echo "Assets installed.\n";
        } catch (IOException $e) {
            echo "An error occurred while copying bootstrap files";
        }
    }

} 