<?php










namespace Symfony\Component\HttpKernel\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass as BaseMergeExtensionConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;






class MergeExtensionConfigurationPass extends BaseMergeExtensionConfigurationPass
{
    private $extensions;

    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public function process(ContainerBuilder $container)
    {
        foreach ($this->extensions as $extension) {
            if (!count($container->getExtensionConfig($extension))) {
                $container->loadFromExtension($extension, array());
            }
        }

        parent::process($container);
    }
}
