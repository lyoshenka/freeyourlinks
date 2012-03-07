<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Extension\Core\DataTransformer\DataTransformerChain;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToTimestampTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ArrayToPartsTransformer;

class DateTimeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $parts = array('year', 'month', 'day', 'hour', 'minute');
        $timeParts = array('hour', 'minute');

        $format = 'Y-m-d H:i:00';
        if ($options['with_seconds']) {
            $format = 'Y-m-d H:i:s';

            $parts[] = 'second';
            $timeParts[] = 'second';
        }

        if ($options['date_widget'] !== $options['time_widget']) {
            throw new FormException(sprintf('Options "date_widget" and "time_widget" need to be identical. Used: "date_widget" = "%s" and "time_widget" = "%s".', $options['date_widget'] ?: 'choice', $options['time_widget'] ?: 'choice'));
        }

        if ('single_text' === $options['widget']) {
            $builder->appendClientTransformer(new DateTimeToStringTransformer($options['data_timezone'], $options['user_timezone'], $format));
        } else {
            // Only pass a subset of the options to children
            $dateOptions = array_intersect_key($options, array_flip(array(
                'years',
                'months',
                'days',
                'empty_value',
                'required',
                'invalid_message',
                'invalid_message_parameters',
                'translation_domain',
            )));
            $timeOptions = array_intersect_key($options, array_flip(array(
                'hours',
                'minutes',
                'seconds',
                'with_seconds',
                'empty_value',
                'required',
                'invalid_message',
                'invalid_message_parameters',
                'translation_domain',
            )));

            // If `widget` is set, overwrite widget options from `date` and `time`
            if (isset($options['widget'])) {
                $dateOptions['widget'] = $options['widget'];
                $timeOptions['widget'] = $options['widget'];
            } else {
                if (isset($options['date_widget'])) {
                    $dateOptions['widget'] = $options['date_widget'];
                }

                if (isset($options['time_widget'])) {
                    $timeOptions['widget'] = $options['time_widget'];
                }
            }

            if (isset($options['date_format'])) {
                $dateOptions['format'] = $options['date_format'];
            }

            $dateOptions['input'] = 'array';
            $timeOptions['input'] = 'array';

            $builder
                ->appendClientTransformer(new DataTransformerChain(array(
                    new DateTimeToArrayTransformer($options['data_timezone'], $options['user_timezone'], $parts),
                    new ArrayToPartsTransformer(array(
                        'date' => array('year', 'month', 'day'),
                        'time' => $timeParts,
                    )),
                )))
                ->add('date', 'date', $dateOptions)
                ->add('time', 'time', $timeOptions)
            ;
        }

        if ('string' === $options['input']) {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToStringTransformer($options['data_timezone'], $options['data_timezone'], $format)
            ));
        } elseif ('timestamp' === $options['input']) {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToTimestampTransformer($options['data_timezone'], $options['data_timezone'])
            ));
        } elseif ('array' === $options['input']) {
            $builder->appendNormTransformer(new ReversedTransformer(
                new DateTimeToArrayTransformer($options['data_timezone'], $options['data_timezone'], $parts)
            ));
        }

        $builder->setAttribute('widget', $options['widget']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('widget', $form->getAttribute('widget'));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'input'         => 'datetime',
            'data_timezone' => null,
            'user_timezone' => null,
            'date_widget'   => null,
            'date_format'   => null,
            'time_widget'   => null,
            /* Defaults for date field */
            'years'         => range(date('Y') - 5, date('Y') + 5),
            'months'        => range(1, 12),
            'days'          => range(1, 31),
            /* Defaults for time field */
            'hours'         => range(0, 23),
            'minutes'       => range(0, 59),
            'seconds'       => range(0, 59),
            'with_seconds'  => false,
            // Don't modify \DateTime classes by reference, we treat
            // them like immutable value objects
            'by_reference'  => false,
            // This will overwrite "widget" child options
            'widget'        => null,
            // This will overwrite "empty_value" child options
            'empty_value'   => null,
            // If initialized with a \DateTime object, FieldType initializes
            // this option to "\DateTime". Since the internal, normalized
            // representation is not \DateTime, but an array, we need to unset
            // this option.
            'data_class'    => null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOptionValues(array $options)
    {
        return array(
            'input'       => array(
                'datetime',
                'string',
                'timestamp',
                'array',
            ),
            'date_widget' => array(
                null, // inherit default from DateType
                'single_text',
                'text',
                'choice',
            ),
            'time_widget' => array(
                null, // inherit default from TimeType
                'single_text',
                'text',
                'choice',
            ),
            // This option will overwrite "date_widget" and "time_widget" options
            'widget'     => array(
                null, // default, don't overwrite options
                'single_text',
                'text',
                'choice',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return isset($options['widget']) && 'single_text' === $options['widget'] ? 'field' : 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'datetime';
    }
}
