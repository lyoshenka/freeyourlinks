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

use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\CountryValidator;

class CountryValidatorTest extends LocalizedTestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new CountryValidator();
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

        $this->assertTrue($this->validator->isValid(null, new Country()));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid('', new Country()));
    }

    /**
     * @expectedException Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->isValid(new \stdClass(), new Country());
    }

    /**
     * @dataProvider getValidCountries
     */
    public function testValidCountries($country)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid($country, new Country()));
    }

    public function getValidCountries()
    {
        return array(
            array('GB'),
            array('AT'),
            array('MY'),
        );
    }

    /**
     * @dataProvider getInvalidCountries
     */
    public function testInvalidCountries($country)
    {
        $constraint = new Country(array(
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $country,
            ));

        $this->assertFalse($this->validator->isValid($country, $constraint));
    }

    public function getInvalidCountries()
    {
        return array(
            array('foobar'),
            array('EN'),
        );
    }
}
