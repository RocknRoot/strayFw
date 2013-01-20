<?php
/**
 * @brief Allows to perform a SQL delete query.
 * @author nekith@gmail.com
 */

class strayModelsQueryDelete extends strayModelsAQuery
{
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
   * Execute the constructed query.
   * @return bool true if query has been well executed
   */
  public function Execute()
  {
    $startTime = microtime();
    $query = 'DELETE FROM ';
    if (true === $this->_only)
      $query .= 'ONLY ';
    $query .= $this->_from;
    // clauses
    if (false === empty($this->_where))
      $query .= ' WHERE ' . $this->_where;
    if (false === empty($this->_orderBy))
      $query .= ' ORDER BY ' . $this->_orderBy;
    if (false === empty($this->_limit))
      $query .= ' LIMIT ' . $this->_limit;
    // execute
    $this->_query = $this->_db->GetLink(false)->prepare($query);
    $result = $this->_query->execute($this->_args);
    $this->_queryError = $this->_query->errorInfo();
    if ('00000' != $this->_queryError[0])
      strayLog::fGetInstance()->Error('QueryDelete fail : ' . $this->_queryError[2] . ' (' . $query . ')');
    if ('development' == STRAY_ENV)
      strayProfiler::fGetInstance()->AddQueryLog($this->_db->GetAlias(), $query, $this->_args, microtime() - $startTime);
    return $result;
  }

  /**
   * Set from clause.
   * @param string $from table
   * @return strayModelsQueryDelete this
   */
  public function From($from)
  {
    $this->_from = $from;
    return $this;
  }

  /**
   * Set where clause.
   * @param string $where condition
   * @param strayModelsQueryOpeOr $where condition
   * @return strayModelsQueryDelete this
   */
  public function Where($where)
  {
    if (null != $this->_where)
      $this->_where .= ' AND ';
    if ($where instanceof strayModelsQueryOpeOr)
      $this->_where .= $where->ToSql();
    else
      $this->_where .= $where;
    return $this;
  }

  /**
   * Set order by clause.
   * @param string $order order by argument
   * @param array $order order by arguments
   * @return strayModelsQueryDelete this
   */
  public function OrderBy($order)
  {
    if (null != $this->_orderBy)
      $this->_orderBy .= ', ';
    if (is_array($order) === true)
    {
      $this->_orderBy = null;
      foreach ($order as $column => $e)
        $this->_orderBy .= $key . ' ' . $e . ', ';
      $this->_orderBy = substr($this->_orderBy, 0, -2);
    }
    else
      $this->_orderBy = $order;
    return $this;
  }

  /**
   * Set limit clause.
   * @param string $limit limit arguments
   * @return strayModelsQueryDelete this
   */
  public function Limit($limit)
  {
    $this->_limit = $limit;
    return $this;
  }

  /**
   * Clear query.
   */
  public function Clear()
  {
    parent::Clear();
    unset($this->_from, $this->_limit, $this->_orderBy, $this->_where);
  }
}
