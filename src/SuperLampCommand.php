<?php

namespace IrfanTOOR;

use IrfanTOOR\Command;
use Exception;

class SuperLampCommand extends Command
{
    const NAME        = "super-lamp";
    const DESCRIPTION = "bring your dependency chain to light";
    const VERSION     = "0.1";

    /** 
     * All of the information regarding the require, require-dev and the installed
     *
     * @var array
     */
    protected $info = [];

    public function __construct()
    {
        parent::__construct(
            [
                "name"        => self::NAME, 
                "description" => self::DESCRIPTION,
                "version"     => self::VERSION,
            ]
        );
    }

    public function init()
    {
        $this->addOption('c|check', 'checks against any conflicts');
        $this->addOption('r|req', 'prints a list of all required dependencies');
        $this->addOption('d|dev', 'prints a list of all required for --dev dependencies');

        $this->setOption('verbose', 1);
    }

    public function configure()
    {
        $this->process();
    }

    public function getMax(array $list, $key = null)
    {
        $max = 0;
        
        foreach ($list as $k => $v)
            $max = max($max, strlen($key ? $v : $k));

        return $max;
    }

    public function getList()
    {
        # name, list, description, keywords, type
        $info = json_decode(file_get_contents("composer.json"), true);

        if (file_exists('composer.lock')) {
            $locked = json_decode(file_get_contents("composer.lock"), true);
        } else {
            $locked = [];
        }

        $list = [];

        foreach ($info['require'] ?? [] as $k => $v) {
            $list[$k] = $v;
        }

        foreach ($info['require-dev'] ?? [] as $k => $v) {
            $list[$k] = $v;
        }
        
        foreach ($locked['packages'] ?? [] as $k => $v) {
            $list[$v['name']] = $v['version'];

            foreach ($v['require'] ?? [] as $kk => $vv) {
                $list[$kk] = $vv;
            }

            foreach ($v['require-dev'] ?? [] as $kk => $vv) {
                $list[$kk] = $vv;
            }
        }

        foreach ($locked['packages-dev'] ?? [] as $k => $v) {
            $list[$v['name']] = $v['version'];

            foreach ($v['require'] ?? [] as $kk => $vv) {
                $list[$kk] = $vv;
            }

            foreach ($v['require-dev'] ?? [] as $kk => $vv) {
                $list[$kk] = $vv;
            }
        }

        return $list;
    }

    public function process()
    {        
        if (!file_exists("composer.json")) {
            throw new Exception("composer.json not found");
        }

        # name, list, description, keywords, type
        $info = json_decode(file_get_contents("composer.json"), true);
        
        if (file_exists('composer.lock')) {
            $locked = json_decode(file_get_contents("composer.lock"), true);
        } else {
            $locked = [];
        }

        $packages = array_merge(
            $locked['packages'] ?? [],
            $locked['packages-dev'] ?? [],
        );

        $list = [
            'php' => [
                'version' => '',
            ]
        ];

        foreach (
            array_merge(
                $info['require'] ?? [], 
                $info['require-dev'] ?? []
            ) as $k => $v
        )
        {
            $list[$k] = [
                'version' => $v
            ];
        }

        $list['php']['installed'] = phpversion();

        foreach ($packages as $package) {
            $name = $package['name'];

            if (isset($list[$name])) {
                $list[$name]['installed']   = $package['version'];
                $list[$name]['require']     = $package['require'] ?? [];
                $list[$name]['require-dev'] = $package['require-dev'] ?? [];
            } else {
                $list[$name] = [
                    'version'     => '',
                    'installed'   => $package['version'],
                    'require'     => $package['require'] ?? [],
                    'require-dev' => $package['require-dev'] ?? [],
                ];
            }

            if (array_key_exists('funding', $package))
                $list[$name]['funding'] = true;
        }

        $info['packages'] = $list;
        $this->info = $info;
    }
    
    public function main()
    {
        $lib  = "";
        $info = $this->info;
        $list = $this->getList();

        $max  = $this->getMax($list) + 2;
        $vmax = $this->getMax($list, 'version') + 2;

        # name, description, keywords and type
        $this->writeln($info['name'] ?? "unknown" . PHP_EOL, 'info');
        $this->writeln();
        $this->writeln($info['description'] ?? "no descrition found in the composer.json" . PHP_EOL);
        $this->writeln();
        
        $keywords = $info['keywords'] ?? null;
        
        if ($keywords) {
            $this->write('keywords    : ', 'green');
            $this->writeln(implode(', ', $keywords));
        }

        $this->write('type        : ', 'green');
        $this->writeln($info['type'] ?? "unknown");

        # authors
        $authors = $info['authors'] ?? null;

        if ($authors) {
            $sep = 'authors     : ';

            foreach ($authors as $item) {
                $this->write($sep, 'green');
                $this->writeln($item['name'] . (isset($item['email']) ? "<" . $item['email'] . ">" : ""));
                $sep = '              ';
            }
        }

        # require
        $require = $info['require'] ?? null;

        if ($require) {
            $this->writeln();
            $sep = 'require     : ';

            foreach ($info['require'] ?? [] as $name => $ver) {
                $package = $info['packages'][$name];
                $installed = $package['installed'] ?? "";
                
                $this->write($sep, 'green');
                $this->write(substr($name . str_repeat(' ', $max), 0, $max) . " ", "blue");
                $this->write($ver . str_repeat(' ', abs($vmax - strlen($ver))), 'green');
                
                if ($installed)
                    $this->writeln(" [" . $installed . "]" . (strlen($installed) <= 12 ? str_repeat(' ', 12 - strlen($installed)) : ' '), 'yellow');
                else
                    $this->writeln(str_repeat(' ', 12));

                $sep = '              ';
            }
        }
        
        # require-dev
        $require_dev = $info['require-dev'] ?? null;

        if ($require_dev) {
            $this->writeln();
            $sep = 'require-dev : ';

            foreach ($info['require-dev'] ?? [] as $name => $ver) {
                // $ver = $package['version'];
                $package = $info['packages'][$name];
                $installed = $package['installed'] ?? "";
                
                $this->write($sep, 'green');
                $this->write(substr($name . str_repeat(' ', $max), 0, $max) . " ", "info");
                $this->write($ver . str_repeat(' ', abs($vmax - strlen($ver))), 'green');
                
                if ($installed)
                    $this->writeln(" [" . $installed . "]" . (strlen($installed) <= 12 ? str_repeat(' ', 12 - strlen($installed)) : ' '), 'yellow');
                else
                    $this->writeln(str_repeat(' ', 12));

                $sep = '              ';
            }
        }

        # dependency chain
        $this->writeln();
        $this->writeln('d-chain     : ', 'green');
            
        foreach ($info['packages'] ?? [] as $name => $package) {
            if ($name === 'php')
                continue;

            $ver = $package['version'];
            $installed = $package['installed'] ?? "";
            $req = $package['require'] ?? [];
            $dev = $package['require-dev'] ?? [];
            $sep = isset($package['funding']) ? '            $ ' : '              ';
            $this->write($sep, 'green');
            $sep = '              ';

            $this->write(substr($name . str_repeat(' ', $max), 0, $max) . " ", "info");
            $this->write($ver . str_repeat(' ', abs($vmax - strlen($ver))), 'green');
            
            if ($installed)
                $this->writeln(" [" . $installed . "]" . (strlen($installed) <= 12 ? str_repeat(' ', 12 - strlen($installed)) : ' '), 'yellow');
            else
                $this->writeln(str_repeat(' ', 12));

            foreach ($req as $name => $ver) {
                $name = '- ' . $name;
                $this->write($sep);
                $this->write(substr($name . str_repeat(' ', $max), 0, $max) . " ", "light_gray");
                $this->writeln($ver . str_repeat(' ', abs($vmax - strlen($ver))), 'light_gray');
            }

            foreach ($dev as $name => $ver) {
                $name = '- ' . $name;
                $this->write($sep);
                $this->write(substr($name . str_repeat(' ', $max), 0, $max) . " ", "dark_gray");
                $this->writeln($ver . str_repeat(' ', abs($vmax - strlen($ver))), 'dark_gray');
            }
        }
    }
}
