<h1 align="center">SqlApi</h1>
<p align="center">SqlApi made in php easy sqlite operation.</p>
<p align="center">You can easily set the folder field names and use them in your project.</p>

```php
require "SqlApi.php";

class ApiTest
{
    private SqlApi $sql;

    /**
     * @throws SqlException
     */
    public function __construct()
    {
        $this->sql = new SqlApi("./test.db","playerTable");

        //SQL Connect
        $this->sql->connect();

        //CREATE TABLE
        $this->sql->createTable(["playerName TEXT NOT NULL PRIMARY KEY, playerMoney INT NOT NULL DEFAULT 0"]);

        //SQL Data insert
        $this->sql->insert(["playerName" => "test1"]);
        $this->sql->insert(["playerName" => "test2"]);

        //SQL Data update where conditions
        $this->sql->update([
            "playerName" => [
                "newData" => "test3",
                "whereArray" => ["playerName" => "test1"]
            ]
        ]);

        //SQL Column delete where conditions
        $this->sql->delete(["playerName" => "test1"]);

        //SQL get Columns or get Tables
        print_r($this->sql->get(SqlApi::COLUMN_MODE, "playerName", [], SQLITE3_ASSOC));

        //SQL Connection close(Disconnect)
        $this->sql->disconnect();
    }
}
new ApiTest();
```
