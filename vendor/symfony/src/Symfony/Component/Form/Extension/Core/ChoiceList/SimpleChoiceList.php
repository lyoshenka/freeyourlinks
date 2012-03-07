<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\ChoiceList;


use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * A choice list for choices of type string or integer.
 *
 * Choices and their associated labels can be passed in a single array. Since
 * choices are passed as array keys, only strings or integer choices are
 * allowed.
 *
 * <code>
 * $choiceList = new SimpleChoiceList(array(
 *     'creditcard' => 'Credit card payment',
 *     'cash' => 'Cash payment',
 * ));
 * </code>
 *
 * The default value generation strategy is `ChoiceList::COPY_CHOICE`, because
 * choice values must be scalar, and the choices passed to this choice list
 * are guaranteed to be scalar.
 *
 * The default index generation strategy is `ChoiceList::GENERATE`, so that
 * your choices can also contain values that are illegal as indices. If your
 * choices are guaranteed to start with a letter, digit or underscore and only
 * contain letters, digits, underscores, hyphens and colons, you can set the
 * strategy to `ChoiceList::COPY_CHOICE` instead.
 *
 * <code>
 * $choices = array(
 *     'creditcard' => 'Credit card payment',
 *     'cash' => 'Cash payment',
 * );
 *
 * // value generation: COPY_CHOICE (the default)
 * // index generation: COPY_CHOICE (custom)
 * $choiceList = new SimpleChoiceList($choices, array(), ChoiceList::COPY_CHOICE, ChoiceList::COPY_CHOICE);
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SimpleChoiceList extends ChoiceList
{
    /**
     * Creates a new simple choice list.
     *
     * @param array   $choices          The array of choices with the choices as keys and
     *                                  the labels as values. Choices may also be given
     *                                  as hierarchy of unlimited depth. Hierarchies are
     *                                  created by creating nested arrays. The title of
     *                                  the sub-hierarchy is stored in the array
     *                                  key pointing to the nested array.
     * @param array   $preferredChoices A flat array of choices that should be
     *                                  presented to the user with priority.
     * @param integer $valueStrategy    The strategy used to create choice values.
     *                                  One of COPY_CHOICE and GENERATE.
     * @param integer $indexStrategy    The strategy used to create choice indices.
     *                                  One of COPY_CHOICE and GENERATE.
     */
    public function __construct(array $choices, array $preferredChoices = array(), $valueStrategy = self::COPY_CHOICE, $indexStrategy = self::GENERATE)
    {
        // Flip preferred choices to speed up lookup
        parent::__construct($choices, $choices, array_flip($preferredChoices), $valueStrategy, $indexStrategy);
    }

    /**
     * Recursively adds the given choices to the list.
     *
     * Takes care of splitting the single $choices array passed in the
     * constructor into choices and labels.
     *
     * @param array $bucketForPreferred
     * @param array $bucketForRemaining
     * @param array $choices
     * @param array $labels
     * @param array $preferredChoices
     *
     * @throws UnexpectedTypeException
     *
     * @see parent::addChoices
     */
    protected function addChoices(&$bucketForPreferred, &$bucketForRemaining, $choices, $labels, array $preferredChoices)
    {
        // Add choices to the nested buckets
        foreach ($choices as $choice => $label) {
            if (is_array($label)) {
                // Don't do the work if the array is empty
                if (count($label) > 0) {
                    $this->addChoiceGroup(
                        $choice,
                        $bucketForPreferred,
                        $bucketForRemaining,
                        $label,
                        $label,
                        $preferredChoices
                    );
                }
            } else {
                $this->addChoice(
                    $bucketForPreferred,
                    $bucketForRemaining,
                    $choice,
                    $label,
                    $preferredChoices
                );
            }
        }
    }

    /**
     * Returns whether the given choice should be preferred judging by the
     * given array of preferred choices.
     *
     * Optimized for performance by treating the preferred choices as array
     * where choices are stored in the keys.
     *
     * @param mixed $choice The choice to test.
     * @param array $preferredChoices An array of preferred choices.
     */
    protected function isPreferred($choice, $preferredChoices)
    {
        // Optimize performance over the default implementation
        return isset($preferredChoices[$choice]);
    }

    /**
     * Converts the choice to a valid PHP array key.
     *
     * @param mixed $choice The choice.
     *
     * @return string|integer A valid PHP array key.
     */
    protected function fixChoice($choice)
    {
        return $this->fixIndex($choice);
    }


    /**
     * Converts the choices to valid PHP array keys.
     *
     * @param array $choices The choices.
     *
     * @return array Valid PHP array keys.
     */
    protected function fixChoices(array $choices)
    {
        return $this->fixIndices($choices);
    }
}
