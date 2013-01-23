<?php
/**
 * @brief Allows to perform a SQL select query.
 * @author nekith@gmail.com
 */
class strayModelsQuerySelect extends strayModelsAQuery
{
  /**
   * Select clause.
   * @var string
   */
  protected $_select;
  /**
   * From clause.
   * @var string
   */
  protected $_from;
  /**
   * Where clause.
   * @var string
   */
  protected $_where;
  /**
   * Group by clause.
   * @var string
   */
  protected $_groupBy;
  /**
   * Having clause.
   * @var string
   */
  protected $_having;
  /**
   * Order by clause.
   * @var string
   */
  protected $_orderBy;
  /**
   * Limit clause.
   * @var string
   */
  protected $_limit;
  /**
   * Inner join table.
   * @var array
   */
  protected $_innerFrom;
  /**
   * Inner join condition.
   * @var array
   */
  protected $_innerOn;
  /**
   * Left outer join table.
   * @var array
   */
  protected $_leftOuterFrom;
  /**
   * Left outer join condition.
   * @var array
   */
  protected $_leftOuterOn;
  /**
   * Right outer join table.
   * @var array
   */
  protected $_rightOuterFrom;
  /**
   * Right outer join condition.
   * @var array
   */
  protected $_rightOuterOn;
  /**
   * Full outer join table.
   * @var array
   */
  protected $_fullOuterFrom;
  /**
   * Full outer join condition.
   * @var array
   */
  protected $_fullOuterOn;
  /**
   * If true, will be executed on write server.
   * @var bool
   */
  protected $_isCritical;

  /**
   * Constructor.
   * @param strayModelsDatabase $db associated database
   * @param bool $critical if true, will be executed on write server
   */
  public function __construct(strayModelsDatabase $db, $critical)
  {
    $this->_isCritical = $critical;
    parent::__construct($db);
  }

  /**
   * Execute the constructed query.
   * @return bool true if query has been well executed
   */
  public function Execute()
  {
    $startTime = microtime(true);
    if (null == $this->_query)
    {
      $query = $this->__toString();
      // prepare
      $this->_query = $this->_db->GetLink(!$this->_isCritical)->prepare($query);
    }
    $result = $this->_query->execute($this->_args);
    $this->_queryError = $this->_query->errorInfo();
    if ('00000' != $this->_queryError[0])
      strayLog::fGetInstance()->Error('QuerySelect fail : ' . $this->_queryError[2] . ' (' . $query . ')');
    if ('development' == STRAY_ENV)
      strayProfiler::fGetInstance()->AddQueryLog($this->_db->GetAlias(), $query, $this->_args, microtime(true) - $startTime);
    return $result;
  }

  /**
   * Get the next result column.
   * @return array next result
   */
  public function Fetch()
  {
    if (null == $this->_query)
      return null;
    $result = $this->_query->fetch(PDO::FETCH_ASSOC);
    if ('00000' == $this->GetErrorState() && true === is_array($result))
      return $result;
    return array();
  }

  /**
   * Get all result columns.
   * @return array results
   */
  public function FetchAll()
  {
    $results = $this->_query->fetchAll(PDO::FETCH_ASSOC);
    if ('00000' == $this->GetErrorState() && true === is_array($results))
      return $results;
    return array();
  }

  /**
   * Set select clause.
   * @param string $select selected rows
   * @return strayModelsQuerySelect this
   */
  public function Select($select)
  {
    if (is_array($select) === true)
    {
      $this->_select = null;
      foreach ($select as $key => $e)
      {
        $this->_select .= $e;
        if (false === is_numeric($key))
          $this->_select .= ' AS ' . $key;
        $this->_select .= ', ';
      }
      $this->_select = substr($this->_select, 0, -2);
    }
    else
      $this->_select = $select;
    return $this;
  }

  /**
   * Add table name in select.
   * @param string $name table
   * @return strayModelsQuerySelect this
   */
  public function SelectAddTableName($name = null)
  {
    if (null == $name)
      $name = $this->_from;
    if (false === is_string($name))
      return $this;
    if (null != $this->_select)
      $this->_select .= ', ';
    $this->_select .= 'pg_class.relname AS table_name';
    $this->InnerJoin('pg_class', $name . '.tableoid = pg_class.oid');
    return $this;
  }

  /**
   * Set from clause.
   * @param string $from table
   * @return strayModelsQuerySelect this
   */
  public function From($from)
  {
    $this->_from = $from;
    return $this;
  }

  /**
   * Set from clause and only at true.
   * @param string $from table
   * @return strayModelsQuerySelect this
   */
  public function FromOnly($from)
  {
    $this->_from = $from;
    $this->SetOnly(true);
    return $this;
  }

  /**
   * Set where clause.
   * @param string $where condition
   * @param strayModelsQueryAOpe $where condition
   * @return strayModelsQuerySelect this
   */
  public function Where($where)
  {
    if (null != $this->_where)
      $this->_where .= ' AND ';
    if ($where instanceof strayModelsQueryAOpe)
      $this->_where .= $where->ToSql();
    else
      $this->_where .= $where;
    return $this;
  }

  /**
   * Set group by clause.
   * @param string $group group by arguments
   * @return strayModelsQuerySelect this
   */
  public function GroupBy($group)
  {
    $this->_groupBy = $group;
    return $this;
  }

  /**
   * Set having clause.
   * @param string $having having conditions
   * @param strayModelsQueryAOpe $having having conditions
   * @return strayModelsQuerySelect this
   */
  public function Having($having)
  {
    if ($having instanceof strayModelsQueryAOpe)
      $this->_having = $having->ToSql();
    else
      $this->_having = $having;
    return $this;
  }

  /**
   * Set order by clause.
   * @param string $order order by argument
   * @param array $order order by arguments
   * @return strayModelsQuerySelect this
   */
  public function OrderBy($order)
  {
    if (null != $this->_orderBy)
      $this->_orderBy .= ', ';
    if (is_array($order) === true)
    {
      foreach ($order as $column => $e)
        $this->_orderBy .= $column . ' ' . $e . ', ';
      $this->_orderBy = substr($this->_orderBy, 0, -2);
    }
    else
      $this->_orderBy .= $order;
    return $this;
  }

  /**
   * Set limit clause.
   * @param string $limit limit arguments
   * @return strayModelsQuerySelect this
   */
  public function Limit($limit)
  {
    $this->_limit = $limit;
    return $this;
  }

  /**
   * Create an inner join.
   * @param string $from inner join table
   * @param string $on inner join condition
   * @param strayModelsQueryAOpe $on inner join condition
   * @return strayModelsQuerySelect this
   */
  public function InnerJoin($from, $on)
  {
    $this->_innerFrom[] = $from;
    if ($on instanceof strayModelsQueryAOpe)
      $this->_innerOn[] .= $on->ToSql();
    else
      $this->_innerOn[] = $on;
    return $this;
  }

  /**
   * Create a left outer join.
   * @param string $from left outer join table
   * @param string $on left outer join condition
   * @param strayModelsQueryAOpe $on left outer join condition
   * @return strayModelsQuerySelect this
   */
  public function LeftOuterJoin($from, $on)
  {
    $this->_leftOuterFrom[] = $from;
    if ($on instanceof strayModelsQueryAOpe)
      $this->_leftOuterOn[] = $on->ToSql();
    else
      $this->_leftOuterOn[] = $on;
    return $this;
  }

  /**
   * Create a right outer join.
   * @param string $from right outer join table
   * @param string $on right outer join condition
   * @param strayModelsQueryAOpe $on right outer join condition
   * @return strayModelsQuerySelect this
   */
  public function RightOuterJoin($from, $on)
  {
    $this->_rightOuterFrom[] = $from;
    if ($on instanceof strayModelsQueryAOpe)
      $this->_rightOuterOn[] = $on->ToSql();
    else
      $this->_rightOuterOn[] = $on;
    return $this;
  }

  /**
   * Create a full outer join.
   * @param string $from full outer join table
   * @param string $on full outer join condition
   * @param strayModelsQueryAOpe $on full outer join condition
   * @return strayModelsQuerySelect this
   */
  public function FullOuterJoin($from, $on)
  {
    $this->_fullOuterFrom[] = $from;
    if ($on instanceof strayModelsQueryAOpe)
      $this->_fullOuterOn[] = $on->ToSql();
    else
      $this->_fullOuterOn[] = $on;
    return $this;
  }

  /**
   * Clear query.
   */
  public function Clear()
  {
    parent::Clear();
    unset($this->_from, $this->_fullOuterFrom, $this->_fullOuterOn,
        $this->_groupBy, $this->_having, $this->_innerFrom, $this->_innerOn,
        $this->_leftOuterFrom, $this->_leftOuterOn, $this->_limit,
        $this->_orderBy, $this->_query, $this->_rightOuterFrom,
        $this->_rightOuterOn, $this->_select, $this->_where);
  }

  /**
   * __toString magic method. Doesn't include args.
   * @return string string value
   */
  public function __toString()
  {
    $query = 'SELECT ' . $this->_select . ' FROM ';
    if (true === $this->_only)
      $query .= 'ONLY '; 
    $query .= $this->_from;
    // joins
    if (true === is_array($this->_innerFrom))
    {
      $max = count($this->_innerFrom);
      for ($i = 0; $i < $max; ++$i)
        $query .= ' INNER JOIN ' . $this->_innerFrom[$i] . ' ON ' . $this->_innerOn[$i];
    }
    if (true === is_array($this->_leftOuterFrom))
    {
      $max = count($this->_leftOuterFrom);
      for ($i = 0; $i < $max; ++$i)
      $query .= ' LEFT OUTER JOIN ' . $this->_leftOuterFrom[$i] . ' ON '
          . $this->_leftOuterOn[$i];
    }
    elseif (true === is_array($this->_rightOuterFrom))
    {
      $max = count($this->_rightOuterFrom);
      for ($i = 0; $i < $max; ++$i)
      $query .= ' RIGHT OUTER JOIN ' . $this->_rightOuterFrom[$i] . ' ON '
          . $this->_rightOuterOn[$i];
    }
    elseif (true === is_array($this->_fullOuterFrom))
    {
      $max = count($this->_fullOuter);
      for ($i = 0; $i < $max; ++$i)
      $query .= ' FULL OUTER JOIN ' . $this->_fullOuterFrom[$i] . ' ON '
          . $this->_fullOuterOn[$i];
    }
    // other clauses
    if (false === empty($this->_where))
      $query .= ' WHERE ' . $this->_where;
    if (false === empty($this->_groupBy))
      $query .= ' GROUP BY ' . $this->_groupBy;
    if (false === empty($this->_having))
      $query .= ' HAVING ' . $this->_having;
    if (false === empty($this->_orderBy))
      $query .= ' ORDER BY ' . $this->_orderBy;
    if (false === empty($this->_limit))
      $query .= ' LIMIT ' . $this->_limit;
    return $query;
  }
}
