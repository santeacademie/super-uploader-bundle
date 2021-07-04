<?php

namespace Santeacademie\SuperUploaderBundle\Command;

use App\Catalog\Entity\Course;
use App\Customer\Entity\Contact;
use App\Customer\Entity\Registration;
use App\External\Entity\Job;
use App\External\Entity\Trainer;
use Santeacademie\SuperUploaderBundle\Asset\AbstractAsset;
use Santeacademie\SuperUploaderBundle\Asset\Variant\AbstractVariant;
use Santeacademie\SuperUploaderBundle\Asset\Variant\PictureVariant;
use Santeacademie\SuperUploaderBundle\Bridge\UploadableEntityBridge;
use Santeacademie\SuperUploaderBundle\Bridge\UploadablePersistentBridge;
use Santeacademie\SuperUploaderBundle\Model\VariantEntityMap;
use Santeacademie\SuperUploaderBundle\Interface\UploadableInterface;
use Santeacademie\SuperUploaderBundle\Wrapper\FallbackResourceFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateDatabaseVariantMapCommand extends Command
{

    private static $entities = [
        Contact::class,
        Course::class,
        Job::class,
        Registration::class,
        Trainer::class
    ];

    protected static $defaultName = 'santeacademie:super-uploader:generate:dbmap';

    public function __construct(
        private string $appPublicDir,
        private EntityManagerInterface $entityManager,
        private UploadablePersistentBridge $uploadablePersistentBridge,
        private UploadableEntityBridge $uploadableEntityBridge
    )
    {
        parent::__construct();

        $uploadablePersistentBridge->setAbsolutePublicDirEnabled(true);
        $uploadableEntityBridge->setAbsolutePublicDirEnabled(true);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate database variant entity maps based on filesystem upload directory')
            ->setHelp('This command allows you to generate database variant entity maps based on filesystem upload directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln([
            '',
            '==============================',
            'Database Variant Map Generator',
            '==============================',
            '',
        ]);

        $io->write('Build variant entity map items...');
        $counter = 0;

        foreach(self::$entities as $entityClass) {
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

                            $this->entityManager->getRepository(VariantEntityMap::class)->persistVariantEntityMap(
                                $variant,
                                $variantEntityMap
                            );

                            $counter++;
                        }

                    }
                }
            }
        }

        $io->success($counter . ' item(s) created');

        return Command::SUCCESS;
    }

}
