<?php
namespace Ajgl\Bundle\CpmBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand
    extends ContainerAwareCommand
{

    protected $installDir;
    protected $targetDir;
    protected $verbose;

    /**
     * @return string
     */
    abstract protected function getAction();

    protected function configure()
    {
        $this
            ->setName('cpm:'.$this->getAction())
            ->setDescription(ucfirst($this->getAction()).'s the given package')
            ->addOption(
                'cpm_dir',
                'cpm',
                InputOption::VALUE_OPTIONAL,
                'Directory to where the CPM sources are installed'
            )
            ->addOption(
                'cpm_registry',
                'reg',
                InputOption::VALUE_REQUIRED,
                'CPM registry',
                'http://packages.dojofoundation.org/'
            )
            ->addArgument(
                'package',
                InputArgument::OPTIONAL,
                "The package to ".$this->getAction()
            )
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                "The package version to ".$this->getAction()
            )
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                "The target directory"
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->installDir = ($input->getOption('cpm_dir'))?:$this->getContainer()->getParameter('ajgl_cpm.install_dir');
        $this->targetDir = ($input->getArgument('target'))?:$this->getContainer()->getParameter('ajgl_cpm.target_dir');
        $this->verbose = $input->getOption('verbose');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->installDir.'/jars/js.jar') || !file_exists($this->installDir.'/lib/cpm.js')) {
            throw new \RuntimeException("The CPM is not available. You could fix it calling command 'cpm:cpm:install'");
        }
        $this->checkTargetDir();
        chdir($this->targetDir);
        if (($package = $input->getArgument('package')) !== null) {
            $this->executeAction($input, $output, $this->getAction(), $input->getOption('cpm_registry'), $package, $input->getArgument('version'));
        } else {
            $packages = $this->getContainer()->getParameter('ajgl_cpm.packages');
            if (empty($packages)) {
                $output->writeln("<info>No default package configured to {$this->getAction()} </info>");
            } else {
                $output->writeln("Action: <info>{$this->getAction()}</info>");
                foreach ($packages as $package => $data) {
                    $version = (isset($data['version']))?$data['version']:null;
                    $output->writeln(" Package: <info>$package [".(($version)?:'latest')."] ({$data['registry']})</info>");
                    $this->executeAction($input, $output, $this->getAction(), $data['registry'], $package, $version);
                }
            }
        }

    }

    protected function executeAction(InputInterface $input, OutputInterface $output, $action, $registry, $package, $version = null)
    {
        putenv("CPM_PATH=$this->installDir");
        putenv("CPM_REGISTRY=$registry");
        $version = ($version)?' '.$version:'';
        exec('java -classpath "$CPM_PATH/jars/js.jar" org.mozilla.javascript.tools.shell.Main -opt -1 "$CPM_PATH/lib/cpm.js" '.$action.' '.$package.$version, $out);
        foreach($out as $line) {
            $output->writeln('<comment>  * '.$line.'</comment>');
        }
    }

    protected function checkTargetDir()
    {
        if (!is_dir($this->targetDir)) {
            if (false === @mkdir($this->targetDir, 0777, true)) {
                throw new \RuntimeException("Unable to create directory '$target'");
            }
        }
    }
}
