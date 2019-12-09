<?php


namespace Seegurke13\ParameterAutowireBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\TypedReference;

class ParameterAutowirePass extends AbstractRecursivePass
{
    /**
     * @var ParameterBagInterface
     */
    private $bag;

    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                if ($isRoot) {
                    $this->currentId = $k;
                }
                if ($v !== $processedValue = $this->processValue($v, $isRoot)) {
                    $value[$k] = $processedValue;
                }
            }
        } elseif ($value instanceof Definition) {
            if (!$value instanceof Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
                return $value;
            }
            if (!$reflectionClass = $this->container->getReflectionClass($value->getClass(), false)) {
                $this->container->log($this, sprintf('Skipping service "%s": Class or interface "%s" cannot be loaded.', $this->currentId, $value->getClass()));

                return $value;
            }

            $constructor = $this->getConstructor($value, false);
            $parameters = $constructor->getParameters();
            foreach ($parameters as $idx => $parameter) {
                if ($parameter->hasType() === true && $parameter->getType()->isBuiltin() && $this->bag->has($parameter->getName())) {
                    $value->setArgument($idx, $this->bag->get($parameter->getName()));
                }
            }
        }

        return $value;
    }
}