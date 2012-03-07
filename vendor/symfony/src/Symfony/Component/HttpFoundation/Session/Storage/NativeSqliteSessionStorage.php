<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage;

/**
 * NativeSqliteSessionStorage.
 *
 * Driver for the sqlite session save hadlers provided by the SQLite PHP extension.
 *
 * @author Drak <drak@zikula.org>
 */
class NativeSqliteSessionStorage extends AbstractSessionStorage
{
    /**
     * @var string
     */
    private $dbPath;

    /**
     * Constructor.
     *
     * @param string $dbPath  Path to SQLite database file.
     * @param array  $options Session configuration options.
     *
     * @see AbstractSessionStorage::__construct()
     */
    public function __construct($dbPath, array $options = array())
    {
        if (!extension_loaded('sqlite')) {
            throw new \RuntimeException('PHP does not have "sqlite" session module registered');
        }

        $this->dbPath = $dbPath;
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerSaveHandlers()
    {
        ini_set('session.save_handler', 'sqlite');
        ini_set('session.save_path', $this->dbPath);
    }

    /**
     * {@inheritdoc}
     *
     * Sets any values sqlite ini values.
     *
     * @see http://php.net/sqlite.configuration
     */
    protected function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array($key, array('sqlite.assoc_case'))) {
                ini_set($key, $value);
            }
        }

        parent::setOptions($options);
    }
}
