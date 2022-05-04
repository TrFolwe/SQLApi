<?php

namespace EasySql;

use SQLite3;
require "SqlException.php";

class SqlApi
{
    /*** @var SQLite3 */
    private SQLite3 $sql;
    /*** @var bool */
    private bool $sqlStatus;
    /*** @var string */
    private string $tableName;

    public const TABLE_MODE = 1;
    public const COLUMN_MODE = 2;

    /**
     * @param string $sqlPath
     * @param string $tableName
     */
    public function __construct(string $sqlPath, string $tableName)
    {
        try {
            $this->sql = new SQLite3($sqlPath);
            $this->sqlStatus = false;
            $this->tableName = $tableName;
            return $this->sql;
        } catch (\SQLiteException $exception) {
            throw new \SQLiteException($exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        $this->sqlStatus = true;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->sqlStatus;
    }

    /**
     * @return void
     * @throws SqlException
     */
    public function disconnect(): void
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        $this->sqlStatus = false;
    }

    /**
     * @param string $tableName
     * @return void
     * @throws SqlException
     */
    public function setTableName(string $tableName): void
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param array $columns
     * @return bool
     * @throws SqlException
     */
    public function createTable(array $columns): bool
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        return $this->sql->exec("CREATE TABLE IF NOT EXISTS " . $this->tableName . "(" . implode(", ", $columns) . ")");
    }

    /**
     * @param array $columns
     * @return bool
     * @throws SqlException
     */
    public function insert(array $columns): bool
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        return $this->sql->exec("INSERT INTO " . $this->tableName . "(" . implode(", ", array_keys($columns)) . ") VALUES(" . implode(", ", array_map(fn($i) => "'$i'", $columns)) . ")");
    }

    /**
     * @param array $columns
     * @return bool
     * @throws SqlException
     */
    public function update(array $columns): bool
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        if (count(array_filter(array_map(fn($c) => $columns[$c], array_keys($columns)), function ($c) {return array_key_exists("newData", $c);})) == 0) throw new SqlException("'newData' Array key not found!");
        foreach (array_filter(array_map(fn($c) => $columns[$c], array_keys($columns)), function ($c) {return !array_key_exists("whereArray", $c);}) as $v) $columns[$v]["whereArray"] = [];
        return $this->sql->exec("UPDATE " . $this->tableName . " SET " . implode(", ", array_map(function ($c) use ($columns) {
            return $c . " = '" . $columns[$c]["newData"] ."'".(!empty($columns[$c]["whereArray"]) ? " WHERE " . implode(" ", array_map(fn($i) => $i . " = '" . $columns[$c]["whereArray"][$i]."'", array_keys($columns[$c]["whereArray"]))) : "");
        }, array_keys($columns))));
    }

    /**
     * @param array $whereArray
     * @return bool
     * @throws SqlException
     */

    public function delete(array $whereArray = []): bool
    {
        if (!$this->sqlStatus) throw new SqlException("Sql Connection is not connected!");
        return $this->sql->exec("DELETE FROM ".$this->tableName.(!empty($whereArray) ? "WHERE ".implode(" ", array_map(fn($i) => $i." = ".$whereArray[$i],array_keys($whereArray))) : ""));
    }

    /**
     * @param int $mode
     * @param string|null $columnName
     * @param array $whereColumns
     * @param int $sqlMode
     * @return array
     * @throws SqlException
     */
    public function get(int $mode = self::TABLE_MODE, ?string $columnName = null, array $whereColumns = [], int $sqlMode = 3): array
    {
        $array = [];
        if ($mode == self::TABLE_MODE){
            $query = $this->sql->query("SELECT * FROM " . $this->tableName . (!empty($whereColumns) ? implode(", ", array_map(fn($i) => $i . " = " . $whereColumns[$i], array_keys($whereColumns))) : ""));
            while($rows = $query->fetchArray($sqlMode))
                $array[] = $rows;
        }
        else if ($mode == self::COLUMN_MODE) {
            if (!$columnName) throw new SqlException("Column mode selected, column name null can't be");
            $query = $this->sql->query("SELECT " . $columnName . " FROM " . $this->tableName . (!empty($whereColumns) ? implode(", ", array_map(fn($i) => $i . " = " . $whereColumns[$i], array_keys($whereColumns))) : ""));
            while($rows = $query->fetchArray($sqlMode))
                $array[] = $rows[$columnName];
        } else throw new SqlException("Column mode or Table mode should be selected, Mode not found!");
        return $array;
    }
}