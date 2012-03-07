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

use Symfony\Component\Validator\Constraints\File;

class FileValidatorPathTest extends FileValidatorTest
{
    protected function getFile($filename)
    {
        return $filename;
    }

    public function testFileNotFound()
    {
        $constraint = new File(array(
            'notFoundMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ file }}' => 'foobar',
            ));

        $this->assertFalse($this->validator->isValid('foobar', $constraint));
    }
}
