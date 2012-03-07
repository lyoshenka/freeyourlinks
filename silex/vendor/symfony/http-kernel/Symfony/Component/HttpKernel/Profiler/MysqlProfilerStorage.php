<?php










namespace Symfony\Component\HttpKernel\Profiler;






class MysqlProfilerStorage extends PdoProfilerStorage
{
    


    protected function initDb()
    {
        if (null === $this->db) {
            if (0 !== strpos($this->dsn, 'mysql')) {
                throw new \RuntimeException('Please check your configuration. You are trying to use Mysql with a wrong dsn. "'.$this->dsn.'"');
            }

            if (!class_exists('PDO') || !in_array('mysql', \PDO::getAvailableDrivers(), true)) {
                throw new \RuntimeException('You need to enable PDO_Mysql extension for the profiler to run properly.');
            }

            $db = new \PDO($this->dsn, $this->username, $this->password);
            $db->exec('CREATE TABLE IF NOT EXISTS sf_profiler_data (token VARCHAR(255) PRIMARY KEY, data LONGTEXT, ip VARCHAR(64), method VARCHAR(6), url VARCHAR(255), time INTEGER UNSIGNED, parent VARCHAR(255), created_at INTEGER UNSIGNED, KEY (created_at), KEY (ip), KEY (method), KEY (url), KEY (parent))');

            $this->db = $db;
        }

        return $this->db;
    }

    


    protected function buildCriteria($ip, $url, $limit, $method)
    {
        $criteria = array();
        $args = array();

        if ($ip = preg_replace('/[^\d\.]/', '', $ip)) {
            $criteria[] = 'ip LIKE :ip';
            $args[':ip'] = '%'.$ip.'%';
        }

        if ($url) {
            $criteria[] = 'url LIKE :url';
            $args[':url'] = '%'.addcslashes($url, '%_\\').'%';
        }

        if ($method) {
            $criteria[] = 'method = :method';
            $args[':method'] = $method;
        }

        return array($criteria, $args);
    }
}
