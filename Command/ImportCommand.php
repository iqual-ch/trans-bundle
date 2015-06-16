<?php

namespace TransBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Translation\Loader\LoaderInterface;
use TransBundle\Entity\Message;
use TransBundle\Entity\Translation;

class ImportCommand extends ContainerAwareCommand
{

    /**
     * @var array
     */
    protected $catalogs = array();

    /**
     * @var array
     */
    protected $cache = array();

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    protected function configure()
    {
        $this
            ->setName('trans:import')
            ->setDescription('Import translations from all bundles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->loadCatalogs($output);
    }

    protected function loadCatalogs(OutputInterface $output)
    {
        /* @var $kernel Kernel */
        $kernel = $this->getContainer()->get('kernel');
        $formats = $this->getContainer()->get('translation.writer')->getFormats();
        $bundles = $this->getContainer()->getParameter('kernel.bundles');

        $paths = array();
        foreach ($bundles as $name => $class) {
            $bundle = $kernel->getBundle($name);
            $path = $bundle->getPath() . '/Resources/translations';
            
            if (is_dir($path)) {
                $output->writeln('Scheduling bundle <comment>' . $name . '</comment> for import.');
                $paths[] = $path;
            }
        }
        
        if (is_dir('app/Resources/translations')) {
            $paths[] = 'app/Resources/translations';
        }

        $finder = new Finder;
        $files = $finder->files()->in($paths)->name(sprintf('/.%s/', join('|', $formats)));
        foreach ($files as $file) {
            $this->importFileToDatabase($output, $file);
        }
    }

    protected function importFileToDatabase(OutputInterface $output, SplFileInfo $file)
    {
        $output->writeln('Processing file: <comment>' . $file->getRealPath() . '</comment>.');
        $matches = array();
        preg_match('/(?P<domain>[\w]+).(?P<locale>[\w]+)/', $file->getBasename(), $matches);
        /* @var $loader LoaderInterface */
        $loader = $this->getContainer()->get('translation.loader.' . strtolower($file->getExtension()));
        $catalogue = $loader->load($file->getRealPath(), $matches['locale'], $matches['domain']);

        $totalTranslations = 0;

        foreach ($catalogue->all() as $domain => $messages) {
            foreach ($messages as $id => $text) {
                $message = $this->entityManager->getRepository('TransBundle:Message')->findOneBy(array(
                    'message' => $id,
                    'domain' => $domain
                ));
                if (!$message) {
                    $message = new Message;
                    $message->setMessage($id);
                    $message->setDomain($domain);
                    $message->setFilename($file->getRealPath());
                    $this->entityManager->persist($message);
                }

                if (!$message->hasTranslation($catalogue->getLocale())) {
                    $translation = new Translation($catalogue->getLocale(), $text);
                    $message->addTranslation($translation);
                    $totalTranslations++;
                }
            }
            $this->entityManager->flush();
        }

        $output->writeln(sprintf('Imported <comment>%d</comment> translations.', $totalTranslations));
    }

}
