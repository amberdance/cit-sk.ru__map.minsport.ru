<?php

namespace Citsk\Library;

use Citsk\Exceptions\DataBaseException;
use PDO;
use PDOException;
use PDOStatement;

final class PDOBase
{

    /**
     * @return PDO
     */
    public function getInstance(): PDO
    {

        try {
            $instance = new PDO(MYSQL['connection_string'], MYSQL['db_user'], MYSQL['db_password']);
            $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $instance;
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param array|null $args
     * @param bool $isReturnPDO
     *
     * @return mixed
     */
    public function executeQuery(string $query, ?array $args = null, bool $isReturnPDO = false)
    {

        try {
            $instance = $this->getInstance();

            if (!$args) {

                if ($isReturnPDO) {
                    return [
                        'PDO'       => $instance,
                        'STATEMENT' => $instance->query($query),
                    ];
                }

                return $instance->query($query);
            }

            $statement = $instance->prepare($query);

            if ($statement->execute($args)) {

                if ($isReturnPDO) {
                    return [
                        'PDO'       => $instance,
                        'STATEMENT' => $statement,
                    ];
                }

                return $statement;
            }

        } catch (PDOException $e) {
            if (strripos($e->getMessage(), 'Duplicate entry')) {
                throw new DataBaseException('Duplicate entry', 102);
            }

            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * @param string|null $query
     * @param array|null $args
     * @param PDO|null $instance
     *
     * @return int
     */
    public function getLastInsertId(?string $query = null, ?array $args = null, ?PDO $instance = null): int
    {
        try {

            if ($instance) {
                return (int) $instance->lastInsertId();
            }

            $instance  = $this->getInstance();
            $statement = $instance->prepare($query);

            if ($args) {
                $statement->execute($args);
            }

            return (int) $instance->lastInsertId();

        } catch (PDOException $e) {
            if (strripos($e->getMessage(), 'Duplicate entry')) {
                throw new DataBaseException('Duplicate entry', 102);
            }

            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param int $fetchType
     * @param PDOStatement|null $statement
     *
     * @return mixed
     */
    public function fetch(string $query, int $fetchType, ?PDOStatement $statement = null)
    {
        try {
            return $statement
            ? $statement->fetch($fetchType)
            : $this->getInstance()->query($query)->fetch($fetchType);
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param int $fetchType
     * @param PDOStatement|null $statement
     *
     * @return array|null
     */
    public function fetchAll(string $query, int $fetchType, ?PDOStatement $statement = null): ?array
    {

        try {
            return $statement
            ? $statement->fetchAll($fetchType)
            : $this->getInstance()->query($query)->fetchAll($fetchType);
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage());
        }

    }

    /**
     * @param string $query
     * @param PDOStatement|null $statement
     *
     * @return string|null
     */
    public function fetchColumn(string $query, ?PDOStatement $statement = null): ?string
    {
        try {

            return $statement
            ? $statement->fetchColumn()
            : $this->getInstance()->query($query)->fetchColumn();
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * @param string|null $query
     * @param PDOStatement|null $statement
     *
     * @return int
     */
    public function rowCount(?string $query, ?PDOStatement $statement = null): int
    {

        try {

            if ($statement) {
                return $statement->rowCount();
            }

            $instance  = $this->getInstance();
            $statement = $instance->prepare($query);
            $statement->execute();

            return $statement->rowCount();
        } catch (PDOException $e) {
            throw new DataBaseException($e->getMessage());
        }

    }
}
