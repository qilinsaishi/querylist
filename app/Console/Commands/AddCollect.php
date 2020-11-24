<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class AddCollect extends GeneratorCommand
{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    //php artisan make:collect page/site1
    protected $name = 'make:collect';
    /**
     * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'Create a new collect class';
    /**
     * 生成类的类型
     *
     * @var string
     */
    protected $type = 'Collect';
    /**
     * 获取生成器的存根文件
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/collect.stub';
    }

    /**
     * 获取类的默认命名空间
     *
     * @param string $rootNamespace
     * @return string
     */

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Collect';
    }
}
