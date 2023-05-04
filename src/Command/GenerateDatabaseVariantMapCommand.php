<?php

namespace Santeacademie\SuperUploaderBundle\Command;


use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Repository\VariantEntityMapRepository;
use Santeacademie\SuperUploaderBundle\Wrapper\FallbackResourceFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateDatabaseVariantMapCommand extends Command
{



    protected static string $commandName = 'santeacademie:super-uploader:generate:dbmap';

    public function __construct(
        private string $appPublicDir,
        private EntityManagerInterface $entityManager,
        private UploadablePersistentBridge $uploadablePersistentBridge,
        private UploadableEntityBridge $uploadableEntityBridge,
        private ?VariantEntityMapRepository $variantEntityMapRepository
    )
    {
        parent::__construct(self::$commandName);

        $uploadablePersistentBridge->setAbsolutePublicDirEnabled(true);
        $uploadableEntityBridge->setAbsolutePublicDirEnabled(true);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate database variant entity maps based on filesystem upload directory')
            ->setHelp('This command allows you to generate database variant entity maps based on filesystem upload directory')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Classes separated by comma');
        ;
    }

    private static function normalizeClass(?string $class): string
    {
        return str_replace('/', '\\', $class ?? '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $classes = [];
        $class = self::normalizeClass($input->getOption('class'));

        if (empty($class)) {
            do {
                $class = self::normalizeClass($io->askQuestion(new Question('What class do you want to add ? (empty for skip)', '')));

                if (class_exists($class)) {
                    $classes[] = $class;
                } elseif (!empty($class)) {
                    $io->warning(sprintf('Class \'%s\' doesn\'t exist.', $class));
                }
            } while(!empty($class));
        } else {
            if (class_exists($class)) {
                $classes[] = $class;
            } elseif (!empty($class)) {
                $io->error(sprintf('Class \'%s\' doesn\'t exist.', $class));
                return Command::FAILURE;
            }
        }

        if (empty($classes)) {
            $io->error('No valid class were given.');
            return Command::FAILURE;
        }

        $output->writeln([
            '',
            '==============================',
            'Database Variant Map Generator',
            '==============================',
            '',
        ]);

        $io->write('Build variant entity map items...');

        foreach($classes as $entityClass) {
            $counter = 0;


            if (!class_implements($entityClass, UploadableInterface::class)) {
                $io->warning(sprintf('Class \'%s\' doesn\'t implements %s. Skipping.', $entityClass, UploadableInterface::class));
                continue;
            }

            /** @var UploadableInterface $entityObject */
            foreach ($this->entityManager->getRepository($entityClass)->findAll() as $entityObject) {
                /** @var UploadableInterface $entityObject */
                foreach($entityObject->getLoadUploadableAssets() as $asset) {
                    /** @var AbstractAsset $asset */
                    foreach($asset->getVariants() as $variant) {
                        /** @var AbstractVariant $variant */

                        if (null !== $variant->getVariantFile() && !$variant->getVariantFile() instanceof FallbackResourceFile) {
                            $variantEntityMap = $this->uploadablePersistentBridge->generateVariantEntityMap($entityObject, $variant);

                            // From absolute path (from PHP SAPI == cli) to relative path on (for WEB src)
                            $variantEntityMap->setFullPath(str_replace(
                                $this->appPublicDir.'/',
                                '',
                                $variantEntityMap->getFullPath()
                            ));

                            $this->variantEntityMapRepository->persistVariantEntityMap(
                                $variant,
                                $variantEntityMap
                            );

                            $counter++;
                        }

                    }
                }
            }

            $io->success(sprintf('%s item(s) created for class %s', $counter, $entityClass));
        }


        return Command::SUCCESS;
    }

}
