<?php
namespace Glorpen\Propel\PropelBundle\Query;

/**
 * Allows to create custom joins (values, other tables) at cost of alias auto handling.
 * @author Arkadiusz Dzięgiel
 */
class PlainModelJoin extends \ModelJoin
{

    //just that - handle table names and aliases as arrays
    protected $leftTableName=array();
    protected $leftTableAlias=array();
    protected $rightTableName=array();
    protected $rightTableAlias=array();

    public function addExplicitCondition(
        $leftTableName,
        $leftColumnName,
        $leftTableAlias = null,
        $rightTableName = null,
        $rightColumnName = null,
        $rightTableAlias = null,
        $operator = self::EQUAL
    ) {
        $this->leftTableName[]   = $leftTableName;
        $this->leftTableAlias[]  = $leftTableAlias;
        $this->rightTableName[]  = $rightTableName;
        $this->rightTableAlias[] = $rightTableAlias;
        $this->left     []= $leftColumnName;
        $this->right    []= $rightColumnName;
        $this->operator []= $operator;
        $this->count++;
    }

    public function getLeftTableAliasOrName($index = 0)
    {
        return $this->leftTableAlias[$index] ? $this->leftTableAlias[$index] : $this->leftTableName[$index];
    }

    public function getLeftColumn($index = 0)
    {
        $tableName = $this->getLeftTableAliasOrName($index);
        return $tableName ? $tableName . '.' . $this->left[$index] : $this->left[$index];
    }

    public function getRightTableAliasOrName($index = 0)
    {
        return $this->rightTableAlias[$index] ? $this->rightTableAlias[$index] : $this->rightTableName[$index];
    }

    public function getRightColumn($index = 0)
    {
        $tableName = $this->getRightTableAliasOrName($index);
        return $tableName ? $tableName . '.' . $this->right[$index] : $this->right[$index];
    }

    public function getRightTableWithAlias($index = 0)
    {
        return $this->rightTableAlias[$index] ?
            $this->rightTableName[$index] . ' ' . $this->rightTableAlias[$index]
            : $this->rightTableName[$index];
    }
    public function getLeftTableWithAlias($index = 0)
    {
        return $this->leftTableAlias[$index] ?
            $this->leftTableName[$index] . ' ' . $this->leftTableAlias[$index]
            : $this->leftTableName[$index];
    }

    public function addCondition($left, $right, $operator = self::EQUAL)
    {
        if ($pos = strrpos($left, '.')) {
            list($this->leftTableName[],  $this->left[]) = explode('.', $left);
        } else {
            $this->left[] = $left;
            $this->leftTableName[] = null;
        }
        if ($pos = strrpos($right, '.')) {
            list($this->rightTableName[], $this->right[]) = explode('.', $right);
        } else {
            $this->right[] = $right;
            $this->rightTableName[] = null;
        }
        $this->rightTableAlias[] = null;
        $this->leftTableAlias[] = null;
        $this->operator[] = $operator;
        $this->count++;
    }

    public static function create(
        \ModelCriteria $q,
        $relation,
        $relationAlias = null,
        $joinType = \Criteria::INNER_JOIN
    ) {
        $tableMap = $q->getTableMap();
        $relationMap = $tableMap->getRelation($relation);

        // create a Join object for this join
        $join = new static();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $q->getModelAlias(), $relationAlias);
        if ($previousJoin = $q->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $q->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $q->addJoinObject($join, $relationAlias);
        } else {
            $q->addJoinObject($join, $relationMap->getRightTable()->getName());
        }

        return $join;
    }
}
