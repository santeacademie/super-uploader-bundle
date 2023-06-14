<?php

namespace Santeacademie\SuperUploaderBundle\Command;

use Santeacademie\SuperUploaderBundle\Generator\FallbackResourcesGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FallbackResourcesGeneratorCommand extends Command
{

    protected static string $commandName = 'santeacademie:super-uploader:generate:fallbacks';


    public function __construct(
        private FallbackResourcesGenerator $fallbackResourcesGenerator
    )
    {
        parent::__construct(self::$commandName);
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate fallback resource asset files')
            ->setHelp('This command allows you to generate asset fallback resource files')
            ->addOption('reset', 'r', InputOption::VALUE_NONE, 'Reset all old resources');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $reset = $input->getOption('reset');

        if (!$reset) {
            $reset = $io->confirm('Do you want to reset all old resources', false);
        }

        $output->writeln([
            '',
            '=========================',
            'Asset Resources Generator',
            '=========================',
            '',
        ]);

        $io->write('Creating resources...');
        $classes = $this->fallbackResourcesGenerator->generateAllResources($reset);
        $counter = 0;

        foreach ($classes as $className => $assets) {
            $io->title('Class: ' . $className);

            foreach ($assets as $assetName => $variants) {
                $io->section('Asset: ' . $assetName);

                $io->horizontalTable(array_map(function ($variantName) use (&$counter) {
                    $counter++;
                    return 'Variant: ' . $variantName;
                }, array_keys($variants)), [
                    array_values($variants)
                ]);
            }
        }

        $io->success($counter . ' resource(s) created');

        return Command::SUCCESS;
    }

}
