<?php
namespace Citsk\Library;

use Citsk\Exceptions\DataBaseException;
use Exception;
use PDO;

/**
 * MySQLHelper
 *
 * @property string $queryString
 * @property string $dbTable
 *
 */
class MySQLHelper
{
    /**
     * dbConnection
     *
     * @var PDOBase
     */
    private $dbConnection;

    /**
     * PDOInstance
     *
     * @var \PDO
     */
    private $PDOInstance;

    /**
     * statement
     *
     * @var \PDOStatement
     */
    private $statement;

    /**
     * queryString
     *
     * @property string
     */
    private $queryString;

    /**
     * dbTable
     *
     * @property string
     */
    private $dbTable;

    /**
     * isTransactionBegin
     *
     * @var bool
     */
    private $isTransactionBegin = false;

    /**
     * transactions
     *
     * @var array
     */
    private $transactions;

    public function __construct()
    {
        $this->dbConnection = new PDOBase;
    }

    /**
     * @param string $dbTable
     *
     * @return MySQLHelper
     */
    public function setDbTable(string $dbTable): MySQLHelper
    {
        $this->dbTable = $dbTable;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    /**
     * @return string|null
     */
    public function getDbTable(): ?string
    {
        return $this->dbTable;
    }

    /**
     * @param array|string|null $select
     * @param array|null $filter
     * @param array|null $filters
     * @param array|null $join
     * @param array|null $group
     * @param array|null $sort
     * @param int|null $limiter
     *
     * @return MySQLHelper
     */
    public function getList($select = null, ?array $filter = null, ?array $args = null, ?array $join = null, ?array $group = null, ?array $sort = null, ?int $limiter = null): MySQLHelper
    {

        $this->setFields($select)
            ->setJoinIfExists($join)
            ->setFilterIsExists($filter)
            ->setGroupFieldsIfExists($group)
            ->setSortIfExists($sort)
            ->setLimitIfExists($limiter);

        $this->statement = $this->getConnection()->executeQuery($this->queryString, $args);

        return $this;

    }

    /**
     * @param array $fields
     * @param array|null $args
     * @param bool $isReturnId
     *
     * @return MySQLHelper
     */
    public function add(array $fields, ?array $args = null, bool $isReturnId = false): MySQLHelper
    {

        $this->setInsertFileds($fields);

        if ($isReturnId) {
            $result = $this->getConnection()->executeQuery($this->queryString, $args, true);

            $this->PDOInstance = $result['PDO'];
            $this->statement   = $result['STATEMENT'];

            if ($this->isTransactionBegin) {
                $this->transactions[] = [
                    'query' => $this->queryString,
                    'args'  => $args,
                ];

                return $this;

            }

        } else {
            if ($this->isTransactionBegin) {
                $this->transactions[] = [
                    'query' => $this->queryString,
                    'args'  => $args,
                ];

                return $this;
            }

            $this->statement = $this->getConnection()->executeQuery($this->queryString, $args);
        }

        return $this;

    }

    /**
     * @param string $query
     * @param array|null $args
     *
     * @return void
     */
    public function query(string $query, ?array $args = null): void
    {
        $this->getConnection()->executeQuery($query, $args);
    }

    /**
     * update row query
     *
     * @param  array|null $updateFields
     * @param  array|null $filter
     * @param  array|null $updateArgs
     * @param  array|null $join
     *
     * @return MySQLHelper
     */
    public function update(array $updateFields, ?array $filter = null, ?array $updateArgs = null, ?array $join = null): MySQLHelper
    {

        if ($join) {
            $joinString = $this->setJoinIfExists($join, true);
        }

        $this->setUpdateFields($updateFields, $joinString ?? null)
            ->setFilterIsExists($filter);

        if ($this->isTransactionBegin) {
            $this->transactions[] = [
                'query' => $this->queryString,
                'args'  => $updateArgs,
            ];

        } else {
            $this->statement = $this->getConnection()->executeQuery($this->queryString, $updateArgs);

            $isUpdated = $this->getRowCount();

            if (!boolval($isUpdated)) {
                throw new DataBaseException("Update failed");
            }

        }

        return $this;

    }

    /**
     * delete row query
     *
     * @param  array|null $deleteFields
     * @param  array $filter
     * @param  array|null $filters
     * @param  array|null $join
     *
     * @return MySQLHelper
     */
    public function delete(?array $deleteFields = null, ?array $filter = null, ?array $args = null, ?array $join = null): MySQLHelper
    {

        $this->statement = $this->setFields($deleteFields, 'DELETE')
            ->setJoinIfExists($join)
            ->setFilterIsExists($filter)
            ->getConnection()
            ->executeQuery($this->queryString, $args);

        return $this;
    }

    /**
     * startTransaction
     *
     * @return MySQLHelper
     */
    public function startTransaction(): MySQLHelper
    {
        $this->isTransactionBegin = true;

        return $this;
    }

    /**
     * @return void
     *
     * @throws DataBaseException
     */
    public function executeTransaction(): void
    {

        try {
            $PDOinstance = $this->getConnection()->getInstance();
            $PDOinstance->beginTransaction();

            if ($this->transactions) {
                foreach ($this->transactions as $transaction) {
                    $statement = $PDOinstance->prepare($transaction['query']);
                    $statement->execute($transaction['args']);
                }
            }

            $PDOinstance->commit();

        } catch (Exception $e) {
            $PDOinstance->rollBack();

            throw new DataBaseException($e->getMessage());
        }
    }

    /**
     * getRowCount
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->getConnection()->rowCount(null, $this->statement);
    }

    /**
     * @param int $fetchStyle
     *
     * 2- PDO::FETCH_ASSOC
     * 5- PDO::FETCH_OBJ
     * 7 PDO::FETCH_COLUMN
     *
     * @return array|object|null
     */
    public function getRow(int $fetchType = 2)
    {
        $result = $this->getConnection()->fetch($this->queryString, $fetchType, $this->statement);

        return $result ? $result : null;

    }

    /**
     * @param int $fetchStyle
     * 2- PDO::FETCH_ASSOC
     * 5- PDO::FETCH_OBJ
     * 7 PDO::FETCH_COLUMN
     *
     * @return array|null
     */
    public function getRows(?int $fetchType = 2): ?array
    {
        return $this->getConnection()->fetchAll($this->queryString, $fetchType, $this->statement);

    }

    /**
     * @return string|null
     */
    public function getColumn(): ?string
    {
        return $this->getConnection()->fetchColumn($this->queryString, $this->statement);
    }

    /**
     * @return int|null
     */
    public function getInsertedId(): ?int
    {
        return $this->getConnection()->getLastInsertId(null, null, $this->PDOInstance);
    }

    /**
     * @return void
     */
    public function closeConnection(): void
    {
        $this->dbConnection       = null;
        $this->statement          = null;
        $this->queryString        = null;
        $this->dbTable            = null;
        $this->isTransactionBegin = false;
    }

    /**
     * @return PDOBase
     */
    private function getConnection()
    {

        return $this->dbConnection ?? new PDOBase;
    }

    /**
     * @param array|string|null $fields
     * @param string $action
     *
     * @return MySQLHelper
     */
    private function setFields($fields, string $action = "SELECT"): MySQLHelper
    {
        $queryFields = null;

        if (is_array($fields) && !is_null($fields)) {

            foreach ($fields as $key => $field) {
                $key = is_string($key) ? "$key as" : '';
                $queryFields .= "$key $field, ";
            }

            $queryFields = trim($queryFields, ', ');

        } elseif (is_string($fields)) {
            $queryFields = $fields;
        } else {
            if ($action == 'SELECT') {
                $queryFields = "*";
            }
        }

        $this->queryString = "$action $queryFields FROM {$this->dbTable}";

        return $this;
    }

    /**
     * insert rows query builder
     *
     * @param  array|null $fields
     *
     * @return MySQLHelper
     */
    private function setInsertFileds(?array $fields): MySQLHelper
    {

        $keys   = implode(', ', array_keys($fields));
        $values = implode(', ', array_values($fields));

        $this->queryString = "INSERT INTO {$this->dbTable} ($keys) VALUES ($values)";

        return $this;

    }

    /**
     * update rows query builder
     *
     * @param  array|null $fields
     * @param  array|null $joinUpdateString
     *
     * @return MySQLHelper
     */
    private function setUpdateFields(?array $fields, ?string $joinUpdateString = null): MySQLHelper
    {

        $queryFields = "";

        foreach ($fields as $key => $field) {
            $queryFields .= "$key = $field, ";
        }

        $queryFields        = trim($queryFields, ', ');
        $joinStringIfExists = $joinUpdateString ?? "";

        $this->queryString = "UPDATE {$this->dbTable} $joinStringIfExists  SET $queryFields";

        return $this;
    }

    /**
     * condition statement query builder
     *
     * @param  array|null $filter
     *
     * @return MySQLHelper
     */
    private function setFilterIsExists(?array $filter): MySQLHelper
    {

        if (empty($filter)) {
            return $this;
        } else {
            $filterStroke = 'WHERE ';
            $glue         = 'AND';
            $comparsion   = null;

            foreach ($filter as $key => $field) {
                if (preg_match("/^[^\w\s:']+/", $field, $match)) {
                    $comparsion = $this->getFilterComparsion($match[0]);

                    $field = ($match[0] == "()")
                    ? str_replace($match[0], null, "($field)")
                    : str_replace($match[0], null, $field);

                } else {
                    $comparsion = "=";
                }

                $filterStroke .= "$key $comparsion $field $glue ";
            }

            $filterStroke = trim($filterStroke, "$glue ");
            $filterStroke = str_replace('OR AND', "OR", $filterStroke);

            $this->queryString .= " $filterStroke";

            return $this;
        }
    }

    /**
     * joint statement query builder
     *
     * @param  array|null $join
     * @param  bool $isJoinUpdate
     *
     * @return MySQLHelper|string
     */
    private function setJoinIfExists(?array $join = null, bool $isJoinUpdate = false)
    {

        if (is_null($join)) {
            return $this;
        } else {

            $joinStroke = "";
            $glue       = "LEFT JOIN";

            foreach ($join as $key => $field) {
                if ($key == 'inner') {
                    $glue = 'JOIN';

                    continue;
                }

                $joinStroke .= "$glue $key ON $field ";
            }

            $joinStroke = trim($joinStroke, 'ON ');

            if ($isJoinUpdate) {
                return $joinStroke;
            } else {
                $this->queryString .= " $joinStroke";
            }

            return $this;
        }
    }

    /**
     * order statement query builder
     *
     * @param  array|null $sort
     *
     * @return MySQLHelper
     */
    private function setSortIfExists(?array $sort): MySQLHelper
    {

        if (is_null($sort)) {
            return $this;
        } else {
            $sortStroke = "ORDER BY ";

            foreach ($sort as $key => $field) {
                $sortStroke .= "$key $field, ";

            }

            $sortStroke = trim($sortStroke, ', ');
            $this->queryString .= " $sortStroke";

            return $this;
        }
    }

    /**
     * group by query builder
     *
     * @param  mixed $group
     *
     * @return MySQLHelper
     */
    private function setGroupFieldsIfExists(?array $group): MySQLHelper
    {
        if (is_null($group)) {
            return $this;
        } else {
            $groupStroke = null;

            foreach ($group as $field) {
                $groupStroke .= "GROUP BY $field, ";
            }

            $groupStroke = trim($groupStroke, ', ');
            $this->queryString .= " $groupStroke";

            return $this;
        }
    }

    /**
     * limit rows query builder
     *
     * @param  int $limiter
     *
     * @return void
     */
    private function setLimitIfExists(?int $limiter)
    {
        if (is_null($limiter)) {
            return $this;
        } else {
            $limitStroke = "LIMIT $limiter";
            $this->queryString .= " $limitStroke";

            return $this;
        }
    }

    /**
     * convert symbols tags to condition query statement
     *
     * @param  string $comparsion
     *
     * @return string
     */
    private function getFilterComparsion(string $comparsion): string
    {

        switch ($comparsion) {
            case '<>':
                return "BETWEEN ";

            case "!!":
                return "IS NOT NULL ";

            case "()":
                return "IN ";

            case "!()":
                return "NOT IN ";

            case "%%":
                return "LIKE ";

            case "<=":
                return "<= ";

            case ">=":
                return ">= ";

            case "!=":
                return "!= ";

            default:
                return '';
        }
    }
}
