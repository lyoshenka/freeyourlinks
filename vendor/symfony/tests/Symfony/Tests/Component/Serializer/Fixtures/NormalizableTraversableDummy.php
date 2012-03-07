<?php

namespace Symfony\Tests\Component\Serializer\Fixtures;

use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class NormalizableTraversableDummy extends TraversableDummy implements NormalizableInterface
{
    public function normalize(NormalizerInterface $normalizer, $format = null)
    {
        return array(
            'foo' => 'normalizedFoo',
            'bar' => 'normalizedBar',
        );
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null)
    {
        return array(
            'foo' => 'denormalizedFoo',
            'bar' => 'denormalizedBar',
        );
    }
}
