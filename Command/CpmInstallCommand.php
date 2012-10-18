<?php
namespace Ajgl\Bundle\CpmBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CpmInstallCommand
    extends ContainerAwareCommand
{
    protected $url = 'https://github.com/kriszyp/cpm/zipball/master';
    protected $installDir;
    protected $verbose;
    protected $zipArchive;

    protected function configure()
    {
        $this
            ->setName('cpm:cpm:install')
            ->setDescription('Installs CPM (CommonJS Package Manager)')
            ->addOption(
                'zip_file',
                'z',
                InputOption::VALUE_OPTIONAL,
                'Zip archive with the CPM sources (if not set, it will be downloaded from default location)'
            )
            ->addArgument(
                'install_dir',
                InputArgument::OPTIONAL,
                "Where to install the CPM files"
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->installDir = $input->getArgument('install_dir') ?: $this->getContainer()->getParameter('ajgl_cpm.install_dir');
        $this->verbose = $input->getOption('verbose');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        if (($this->zipArchive = $input->getOption('zip_file')) === null) {
            if ($this->verbose) {
                $output->writeln("  * <comment>Downloading zip archive from '$this->url'</comment>");
            }
            $this->zipArchive = sys_get_temp_dir() . '/' . uniqid('kriszyp-cpm-') . '.zip';
            $this->download($this->url, $this->zipArchive);
        }

        $zip = new \ZipArchive();

        if ($this->verbose) {
            $output->writeln("  * <comment>Opening zip archive '$this->zipArchive'</comment>");
        }
        if (($res = $zip->open($this->zipArchive)) !== true) {
            throw new \RuntimeException("Unable to open zip archive '$this->zipArchive'", $res);
        }

        $basedir = dirname($this->installDir);

        if (!is_dir($basedir)) {
            if ($this->verbose) {
                $output->writeln("  * <comment>Creating install dir '$basedir'</comment>");
            }
            if (false === @mkdir($basedir, 0777, true)) {
                throw new \RuntimeException("Unable to create directory '$basedir'");
            }
        }

        if (is_dir($this->installDir)) {
             if ($this->verbose) {
                $output->writeln("  * <comment>Removing destination dir '$this->installDir'</comment>");
            }
            $fs->remove($this->installDir);
        }

        if ($this->verbose) {
            $output->writeln("  * <comment>Extracting CPM files to '$basedir'</comment>");
        }
        if (($res = $zip->extractTo($basedir)) === false) {
            throw new \RuntimeException("Unable to extract zip archive to '$basedir'");
        }

        if (($data = $zip->statIndex(0)) === false) {
            throw new \RuntimeException("Unable to get details from zip archive for entry at index '0'");
        }
        $cpmdir = $basedir . DIRECTORY_SEPARATOR . $data['name'];
        $cpmdir = (substr($cpmdir, -1) == DIRECTORY_SEPARATOR)?substr($cpmdir, 0, -1):$cpmdir;
        if ($this->verbose) {
            $output->writeln("  * <comment>Renaming '$cpmdir' to '$this->installDir'</comment>");
        }
        $fs->rename($cpmdir, $this->installDir);

        $zip->close();
        @unlink($this->zipArchive);
    }

    protected function download($url, $destination)
    {
        $maxTime = ini_get('max_execution_time');
        set_time_limit(0);
        if (($writeBuffer = fopen($destination, "wb")) === false) {
            throw new \RuntimeException("Unable to open file '$destination' in write binary mode");
        }

        if (!function_exists('curl_init')) {
            if (ini_get('allow_fopen_url') === false) {
                throw new \RuntimeException("'allow_fopen_url' must be enabled or 'curl' extension loaded to download the CPM archive");
            }
            $this->fopenDownload($url, $writeBuffer);
        } else {
            $this->curlDownload($url, $writeBuffer);
        }

        if (fclose($writeBuffer) === false) {
            throw new \RuntimeException("Unable to close write buffer");
        }
        set_time_limit($maxTime);
    }

    protected function curlDownload($url, $buffer)
    {
        $options = array(
          CURLOPT_FILE => $buffer,
          CURLOPT_TIMEOUT => 1800, // set this to 1/2 hour so we dont timeout on big files
          CURLOPT_FOLLOWLOCATION => true
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        curl_close($ch);
    }

    protected function fopenDownload($url, $buffer)
    {
        if (($readBuffer = fopen($url, "rb")) === false) {
            throw new \RuntimeException("Unable to open file '$this->url' in read binary mode");
        }

        while(!feof($readBuffer)) {
            fwrite($buffer, fread($readBuffer, 1024 * 8 ), 1024 * 8 );
        }

        if (fclose($readBuffer) === false) {
            throw new \RuntimeException("Unable to close read buffer");
        }
    }
}
