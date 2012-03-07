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

use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\ImageValidator;

class ImageValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;
    protected $path;
    protected $image;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new ImageValidator();
        $this->validator->initialize($this->context);
        $this->image = __DIR__.'/Fixtures/test.gif';
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid(null, new Image()));
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid('', new Image()));
    }

    public function testValidImage()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->assertTrue($this->validator->isValid($this->image, new Image()));
    }

    public function testValidSize()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Image(array(
            'minWidth' => 1,
            'maxWidth' => 2,
            'minHeight' => 1,
            'maxHeight' => 2,
        ));

        $this->assertTrue($this->validator->isValid($this->image, $constraint));
    }

    public function testWidthTooSmall()
    {
        $constraint = new Image(array(
            'minWidth' => 3,
            'minWidthMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ width }}' => '2',
                '{{ min_width }}' => '3',
            ));

        $this->assertFalse($this->validator->isValid($this->image, $constraint));
    }

    public function testWidthTooBig()
    {
        $constraint = new Image(array(
            'maxWidth' => 1,
            'maxWidthMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ width }}' => '2',
                '{{ max_width }}' => '1',
            ));

        $this->assertFalse($this->validator->isValid($this->image, $constraint));
    }

    public function testHeightTooSmall()
    {
        $constraint = new Image(array(
            'minHeight' => 3,
            'minHeightMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ height }}' => '2',
                '{{ min_height }}' => '3',
            ));

        $this->assertFalse($this->validator->isValid($this->image, $constraint));
    }

    public function testHeightTooBig()
    {
        $constraint = new Image(array(
            'maxHeight' => 1,
            'maxHeightMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ height }}' => '2',
                '{{ max_height }}' => '1',
            ));

        $this->assertFalse($this->validator->isValid($this->image, $constraint));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidMinWidth()
    {
        $constraint = new Image(array(
            'minWidth' => '1abc',
        ));

        $this->validator->isValid($this->image, $constraint);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidMaxWidth()
    {
        $constraint = new Image(array(
            'maxWidth' => '1abc',
        ));

        $this->validator->isValid($this->image, $constraint);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidMinHeight()
    {
        $constraint = new Image(array(
            'minHeight' => '1abc',
        ));

        $this->validator->isValid($this->image, $constraint);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testInvalidMaxHeight()
    {
        $constraint = new Image(array(
            'maxHeight' => '1abc',
        ));

        $this->validator->isValid($this->image, $constraint);
    }
}
