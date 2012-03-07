<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Constraints\LocaleValidator;

class LocaleValidatorTest extends LocalizedTestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new LocaleValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid(null, new Locale()));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid('', new Locale()));
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->isValid(new \stdClass(), new Locale());
    }

    /**
     * @dataProvider getValidLocales
     */
    public function testValidLocales($locale)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid($locale, new Locale()));
    }

    public function getValidLocales()
    {
        return array(
            array('en'),
            array('en_US'),
            array('my'),
            array('zh_Hans'),
        );
    }

    /**
     * @dataProvider getInvalidLocales
     */
    public function testInvalidLocales($locale)
    {
        $constraint = new Locale(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $locale,
            ));

        $this->assertFalse($this->validator->isValid($locale, $constraint));
    }

    public function getInvalidLocales()
    {
        return array(
            array('EN'),
            array('foobar'),
        );
    }
}
