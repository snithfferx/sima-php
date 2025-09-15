<?php
/**
 * Model Class to consume the server local database using a Model of Table
 * @description This class is the base class for all database connections
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @category Class
 * @package CLASSES\Model
 * @version 1.7.0
 * @date 2024-03-11 | 2025-07-29
 * @time 22:30:00
 * @copyright (c) 2024 - 2025 Bytes4Run
 */
declare (strict_types = 1);

namespace SIMA\CLASSES;

use SIMA\CLASSES\Connection;

class Model
{
    private Connection $connection;
    private string $tableName;
    private array $fields;
    private string $joins;
    private array $conditions;
    private string $separator;
    private string $sorting;
    private string $orderby;
    private string $groupby;
    private string $operator;
    private string $query;
    private array $values;
    private array|null $response;
    private array $error;

    private int $limit;
    private int $offset;
    private string $queryType;
    public function __construct()
    {
        $this->connection = new Connection();
    }
    /**
     * Recieve the list of fields to retrieve from table
     * @param array|string $query
     * @return Model
     */
    public function select(
        array | string $query = ['*'], // fields to retrieve from table
    ): Model {
        $this->setFields($query);
        $this->queryType = 'select';
        $this->executeQuery();
        return $this;
    }

    /**
     * Recieve the list of fields to insert into table
     * @param array $values
     * @return Model
     */
    public function insert(
        array $values
    ): Model {
        $this->setValues($values);
        $this->queryType = 'insert';
        $this->executeQuery();
        return $this;
    }

    /**
     * Recieve the list of fields to update into table
     * @param array $values
     * @return Model
     */
    public function update(
        array $values
    ): Model {
        $this->setValues($values);
        $this->queryType = 'update';
        $this->executeQuery();
        return $this;
    }
    /**
     * Recieve the list of fields to delete from table
     * @param array $values
     * @return Model
     */
    public function delete(
        array $values
    ): Model {
        $this->setValues($values);
        $this->queryType = 'delete';
        $this->executeQuery();
        return $this;
    }
    /**
     * Recieve the table name to query
     * @param string $tableName
     * @return Model
     */
    public function from(string $tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Recieve the field name to order by
     * @param string $order
     * @param string|array $field
     * @return Model
     */
    public function orderBy(string $order, string | array $field): Model
    {
        $this->orderby = is_array($field) ? implode(',', $field) : $field;
        $this->sorting = $order;
        return $this;
    }

    /**
     * Recieve the field name to group by
     * @param string $field
     * @return Model
     */
    public function groupBy(string $field): Model
    {
        $this->groupby = $field;
        return $this;
    }

    public function limit(int $limit): Model
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): Model
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Función que establece la condición a ser cumplida para la busqueda.
     *
     * @param string|array $table
     * @param string|array $field
     * @param string|array $value
     * @return string|array
     */
    protected function eq(string | object $table, string | array $field, string | array $value): array
    {
        $this->values[] = $value;
        // Return the condition string with the table and field names and the value to compare
        $vals = [];
        if (is_array($field)) {
            for ($x = 0; $x < (count($field) - 1); $x++) {
                $vals[] = $table . '.' . $field[$x] . ' = ?';
            }
        }
        return is_array($field) ? $vals : [$table . '.' . $field . ' = ?'];
    }

    /**
     * Function to join where statament with AND
     * @param array $value
     * Return null if the array has only one value
     * @return string|null
     */
    protected function  and (array $value): string | null
    {
        if (count($value) == 1) {
            return null;
        }

        return implode(' AND ', $value);
    }
    /**
     * Function to join where statament with OR
     * @param array $value
     * Return null if the array has only one value
     * @return string|null
     */
    protected function  or (array $value): string | null
    {
        if (count($value) == 1) {
            return null;
        }

        return implode(' OR ', $value);
    }
    /**
     * Funcion que establece la condición a ser cumplida para la busqueda.
     *
     * @param string|array $condition
     * @return Model
     */
    protected function where(string | array $condition): Model
    {
        if (is_array($condition)) {
            $this->conditions = $this->getConditions($condition);
        } else {
            $this->conditions = $this->getConditions(explode(',', $condition));
        }
        return $this;
    }

    /**
     * Función que establece el orden de los registros a ser devueltos.
     *
     * @param string $order [ASC,DESC]
     * @param string $orderby Campo por el cual se ordenarán los registros.
     * @return Model
     */
    protected function sorting(string $order = 'ASC', string | array $field = 'id'): Model
    {
        return $this->orderBy($order, $field);
    }

    /**
     * Function to group the result by a field
     * @param string $groupby
     * @return Model
     */
    protected function grouping(string $field = 'id'): Model
    {
        return $this->groupBy($field);
    }

    /**
     * Funcion que devuelve los registros solicitados.
     * @param array|null $values
     * @return array|bool
     * @example select()->from('table')->where('id', 1)->get();
     */
    protected function get(array | null $values = null): array
    {
        $this->executeQuery($values);
        return $this->response;
    }

    /**
     * funcion que realiza la inserción de datos.
     *
     * @param array|null $data
     * @return array|bool
     */
    protected function save(array | null $data = null): array
    {
        $this->executeQuery($data);
        return $this->response;
    }

    protected function join(array $joins)
    {
        if (!empty($joins)) {
            /* $joins=[
            'inner'=>[
            ['table1'=>'field1','table2'=>'field2'],
            ['table1'=>'field1','table2'=>'field2']
            ],
            'left'=>[
            ['table1'=>'field1','table2'=>'field2']
            ]
            ]
             */
            if (isset($joins[0]['type'])) {
                foreach ($joins as $join) {
                    $this->joins .= " $join[type] JOIN `$join[table]` ON `$join[table]`.`$join[filter]` = `$join[compare_table]`.`$join[compare_filter]`";
                }
            } else {
                foreach ($joins as $type => $args) {
                    foreach ($args as $tables) {
                        $this->joins .= " " . $type . " JOIN ";
                        $this->joins .= "`" . key($tables[0]) . "`";
                        $this->joins .= " ON `" . key($tables[0]) . "`.`" . $tables[0] . "` = ";
                        $this->joins .= " `" . key($tables[1]) . "`.`" . $tables[1] . "`";
                    }
                }
            }
        }
    }

    /**
     * Función que establece el separador de una consulta.
     *
     * Puede usarse los siguientes simbolos:
     * "<";"lt";"LessThan" Menor que, para referenciar que se buscaran los resultados menores a $value (a partir de propiedad)
     * ">";"mt";"MoreThan" Mayor que, para referenciar que se buscaran los resultados mayores a $value (a partir de propiedad)
     * "=";"eq";"Equal" Igaul a, para referenciar que se buscan los valores iguales a $value (a partir de propiedad)
     * "!";"!=";"neq";"NOT";"NotEq";"Distint";"Diferent" No igual (Diferente,Distinto), para referenciar que se buscan los valores no iguales a $value
     * "<=";"lte";"LessThanEq" Menor o igual que, para hacer referencia que se buscan los valores menores o iguales a $value
     * ">=";"mte";"MoreThanEq" Mayor o igual que, para hacer referencia que se buscan los valores mayores o iguales a $value
     * "*.*";"btw";"Between" En medio, para hacer referencia que se buscan los valores que contengan en medio $value
     * "*.";"sw";"StartWith" Inicia con, para hacer referencia que se buscan los valores que inicien con $value
     * ".*";"ew";"EndWith" Termina con, para hacer referencia que se buscan los valores que terminen con $value
     * @param string $simbol
     * @param mixed $value
     * @return void
     */
    protected function operator(string $simbol)
    {
        if (!empty($simbol) && $simbol != '') {
            switch ($simbol) {
                case "<":
                case "lt":
                case "LessThan":
                    $this->operator = "<";
                    break;
                case ">":
                case "mt":
                case "MoreThan":
                    $this->operator = ">";
                    break;
                case "=":
                case "eq":
                case "Equal":
                    $this->operator = "=";
                    break;
                case "!":
                case "!=":
                case "neq":
                case "NOT":
                case "NotEq":
                    $this->operator = "!=";
                    break;
                case "Distint":
                case "distint":
                case "DISTINT":
                case "Diferent":
                case "diferent":
                case "DIFERENT":
                    $this->operator = "<>";
                    break;
                case "<=":
                case "lte":
                case "LessThanEq":
                    $this->operator = "<=";
                    break;
                case ">=":
                case "mte":
                case "MoreThanEq":
                    $this->operator = ">=";
                    break;
                case "*.*":
                case "btw":
                case "Between":
                    $this->operator = "BETWEEN";
                    break;
                case "*.":
                case "sw":
                case "StartWith":
                    $this->operator = "LIKE CONCAT(?, '%')";
                    break;
                case ".*":
                case "ew":
                case "EndWith":
                    $this->operator = "LIKE CONCAT('%', ?)";
                    break;
                default:
                    $this->operator = "=";
                    break;
            }
        } else {
            $this->operator = "=";
        }
    }

    /**
     * Función que asigna el separador de condiciones en una consulta.
     *
     * Puede usarse los siguientes simbolos:
     * "Y";"AND";"And";"and" Para hacer referencia a que se deben cumplir todas las condiciones.
     * "O";"OR";"Or";"or" Para hacer referencia a que se debe cumplir al menos una de las condiciones.
     * @param string $simbol
     * @return void
     */
    protected function separator(string $simbol)
    {
        if (!empty($simbol) && $simbol != '') {
            switch ($simbol) {
                case "Y":
                case "AND":
                case "And":
                case "and":
                    $this->separator = "AND";
                    break;
                case "O":
                case "OR":
                case "Or":
                case "or":
                    $this->separator = "OR";
                    break;
                default:
                    $this->separator = "AND";
                    break;
            }
        } else {
            $this->separator = "AND";
        }
    }

    /**
     * Sets the error array with the given error.
     *
     * @param array $error The error to be set.
     * @return void
     */
    private function setError(array $error): Model
    {
        if (!empty($this->error)) {
            array_push($this->error, $error);
        } else {
            $this->error = $error;
        }
        return $this;
    }

    public function executeQuery(null | array $values = null): Model
    {
        if ($values !== null) {
            $this->values = $values;
        }
        // Check query type
        if ($this->queryType == 'select') {
            $this->query = "SELECT ";
            $this->query .= $this->fields;
            $this->query .= " FROM ";
            $this->query .= $this->tableName;
            if (!is_null($this->conditions)) {
                $this->query .= " WHERE ";
                $this->query .= $this->conditions['string'];
                foreach ($this->conditions['values'] as $item) {
                    array_push($this->values, $item);
                }
            }
            if ($this->orderby !== null) {
                $this->query .= " ORDER BY ";
                $this->query .= $this->orderby;
                $this->query .= " ";
                $this->query .= $this->sorting;
            }
            if ($this->limit !== null) {
                $this->query .= " LIMIT ";
                $this->query .= $this->limit;
            }
            if ($this->offset !== null) {
                $this->query .= " OFFSET ";
                $this->query .= $this->offset;
            }
            if ($this->groupby !== null) {
                $this->query .= " GROUP BY ";
                $this->query .= $this->groupby;
            }
        } elseif ($this->queryType == 'insert') {
            $this->query = "INSERT INTO ";
            $this->query .= $this->tableName;
            $this->query .= " (";
            $this->query .= $this->fields;
            $this->query .= ") VALUES (";
            $this->query .= $this->values;
            $this->query .= ")";
        } elseif ($this->queryType == 'update') {
            $this->query = "UPDATE ";
            $this->query .= $this->tableName;
            $this->query .= " SET ";
            $this->query .= $this->values;
            $this->query .= " WHERE ";
            if (!is_null($this->conditions)) {
                $this->query .= $this->conditions['string'];
                foreach ($this->conditions['values'] as $item) {
                    array_push($this->values, $item);
                }
            }
        } elseif ($this->queryType == 'delete') {
            $this->query = "DELETE FROM ";
            $this->query .= $this->tableName;
            $this->query .= " WHERE ";
            if (!is_null($this->conditions)) {
                $this->query .= $this->conditions['string'];
                foreach ($this->conditions['values'] as $item) {
                    array_push($this->values, $item);
                }
            }
        }
        $response = $this->connection->query($this->query, $this->values)->getResponse();
        if ($response !== null) {
            $this->response = $this->interpretateResponse($this->queryType, $response);
        }
        return $this;
    }
	
    public function getResponse()
    {
        return $this->response;
    }

    private function setFields(string | array $fields): Model
    {
        $this->fields = $fields;
        if (is_string($this->fields)) {
            if ($this->fields == "all" || $this->fields == "*") {
                $this->query .= "*";
            } else {
                $this->query .= $this->fields;
            }
        } else {
            if (is_array($this->fields)) {
                foreach ($this->fields as $table => $fields) {
                    if (!empty($fields)) {
                        foreach ($fields as $x => $field) {
                            $asignado = explode("=", $field);
                            $this->query .= (count($asignado) > 1) ? "`$table`.`$asignado[0]` AS '$asignado[1]'" : "`$table`.`$field`";
                            if ($x < (count($fields) - 1)) {
                                $this->query .= ", ";
                            }
                        }
                        unset($field, $x);
                    } else {
                        $this->query .= "`$table`.*";
                    }
                }
                unset($table, $fields);
            } else {
                $this->error = ['status' => 400, 'message' => "The fields type is not supported."];
                return $this;
            }
        }
        return $this;
    }
    /**
     * Function to get the conditions to be used in the query
     * @param array $conditions
     * @return array
     */
    private function getConditions(array $conditions): array
    {
        $string = "";
        $values = [];
        if (isset($conditions['condition']) && !empty($conditions['condition'])) {
            foreach ($conditions['condition'] as $indice => $cond) {
                if ($indice > 0) {
                    $separador = ($conditions['separator'][($indice - 1)]) ?? null;
                    if (isset($separador) && !is_null($separador)) {
                        match ($separador) {
                            "Y" => $string .= " AND ",
                            "O" => $string .= " OR ",
                        };
                    }
                }
                $string .= '`' . $cond['table'] . '`.`' . $cond['field'] . '`';
                match ($cond['type']) {
                    'COMPARATIVE' => $string .= ' = ? ',
                    'SIMILAR' => $string .= " LIKE CONCAT('%', ?, '%') ",
                    'START_WITH' => $string .= " LIKE CONCAT(?, '%') ",
                    'END_WITH' => $string .= " LIKE CONCAT('%', ?) ",
                    'RANGE' => $string .= ' BETWEEN ? AND ? ',
                    'NEGATIVE' => $string .= ' != ? ',
                    'LESS_THAN' => $string .= ' < ? ',
                    'MORE_THAN' => $string .= ' > ? ',
                    'LESS_EQ_TO' => $string .= ' <= ? ',
                    'MORE_EQ_TO' => $string .= ' >= ? ',
                    'NOT_IN' => $string .= function ($cond) use ($string) {
                        $string .= ' NOT IN (';
                        for ($ind = 0; $ind < count($cond['value']); $ind++) {
                            $string .= (($ind + 1) < count($cond['value'])) ? '?,' : '?';
                        }
                        $string .= ')';
                        return $string;
                    },
                    'IS_IN' => $string .= function ($cond) use ($string) {
                        $string .= ' IN (';
                        for ($ind = 0; $ind < count($cond['value']); $ind++) {
                            $string .= (($ind + 1) < count($cond['value'])) ? '?,' : '?';
                        }
                        $string .= ')';
                        return $string;
                    },
                };
                if ($cond['type'] != 'RANGE' && $cond['type'] != 'NOT_IN') {
                    array_push($values, $cond['value']);
                } else {
                    foreach ($cond['value'] as $item) {
                        array_push($values, $item);
                    }
                }
            }
        }
        return ['string' => $string, 'values' => $values];
    }
    /**
     * Obtiene la cuenta, suma, promedio, mínimo o máximo de un campo de una tabla.
     *
     * @param string $table Tabla a realizarle la consulta.
     * @param string $campo Campo por el cual se realizará la consulta.
     * @param array $condicion [$params => [condicion=[['table','type','field','value']], separador=[Y]]] Condición y separador para la consulta.
     * @return array
     */
    private function getDBDataFunction($function, $campo, $condicion)
    {
        $values = [];
        $string = "SELECT ";
        switch ($function) {
            case "min":
                $string .= "MIN";
                break;
            case "max":
                $string .= "MAX";
                break;
            case "avg":
                $string .= "AVG";
                break;
            case "sum":
                $string .= "SUM";
                break;
            case "dist":
                $string .= "DISTINCT";
                break;
            default:
                $string .= "COUNT";
                break;
        }
        if ($function != "dist") {
            $string .= "(?) AS 'res' FROM `" . $this->tableName . "`";
            $values[] = "`" . $this->tableName . "`.`" . $campo . "`";
        } else {
            $string .= "(`$campo`) FROM `$this->tableName`";
        }
        if (!is_null($condicion)) {
            $string .= " WHERE ";
            $conditions = $this->getConditions($condicion);
            $string .= $conditions['string'];
            foreach ($conditions['values'] as $item) {
                array_push($values, $item);
            }
        }
        $string .= ";";
        return $this->interpretateResponse('select',
            $this->connection->query($string, $values)->getResponse());
    }

    private function interpretateResponse(string $request, array $response): array
    {
        $result = ['status' => $response['status'], 'message' => $response['message']];
        $result['data'] = match ($request) {
            "select" => $response['data']['rows'],
            "insert" => $response['data']['id'],
            "update" => $response['data']['affected'],
            "delete" => $response['data']['affected'],
            default => $response['data'],
        };
        return $result;
    }

    private function setValues(array $values): void
    {
        $params = [];
        $vals = [];
        foreach ($values as $key => $value) {
            $params[] = "`$key` = ?";
            $vals[] = $value;
        }
        $this->values = [
            'params' => $params,
            'values' => $vals,
        ];
    }
}
