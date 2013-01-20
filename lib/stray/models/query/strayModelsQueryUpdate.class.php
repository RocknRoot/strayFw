<?php
/**
 * @brief Allows to perform a SQL update query.
 * @author nekith@gmail.com
 */

class strayModelsQueryUpdate extends strayModelsAQuery
{
  /**
   * Update clause.
   * @var string
   */
  protected $_update;
  /**
   * Set clause.
   * @var string
   */
  protected $_set;
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
    $query = 'UPDATE ';
    if (true === $this->_only)
      $query .= 'ONLY ';
    $query .= $this->_update . ' SET ' . $this->_set;
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
      strayLog::fGetInstance()->Error('QueryUpdate fail : ' . $this->_queryError[2] . ' (' . $query . ')');
    if ('development' == STRAY_ENV)
      strayProfiler::fGetInstance()->AddQueryLog($this->_db->GetAlias(), $query, $this->_args, microtime() - $startTime);
    return $result;
  }

  /**
   * Set update clause.
   * @param string $update table
   * @return strayModelsQueryUpdate this
   */
  public function Update($update)
  {
    $this->_update = $update;
    return $this;
  }

  /**
   * Set set clause.
   * @param string $set set arguments
   * @return strayModelsQueryUpdate this
   */
  public function Set($set)
  {
    if (true === is_array($set))
    {
      $this->_set = null;
      foreach ($set as $key => $val)
      {
        if (false !== stripos($key, '.'))
          list($null, $tmp) = explode('.', $key);
        else
          $tmp = $key;
        $this->_set .= $tmp . ' = ' . $val . ', ';
      }
      $this->_set = substr($this->_set, 0, -2);
    }
    else
    {
      if (false !== stripos($set, '.'))
        list($null, $this->_set) = explode('.', $set);
      else
        $this->_set = $set;
    }
    return $this;
  }

  /**
   * Set where clause.
   * @param string $where condition
   * @param strayModelsQueryOpeOr $where condition
   * @return strayModelsQueryUpdate this
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
   * @param string $order order by arguments
   * @return strayModelsQueryUpdate this
   */
  public function OrderBy($order)
  {
    if (null != $this->_orderBy)
      $this->_orderBy .= ', ';
    $this->_orderBy = $order;
    return $this;
  }

  /**
   * Set limit clause.
   * @param string $limit limit arguments
   * @return strayModelsQueryUpdate this
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
    unset($this->_limit, $this->_orderBy, $this->_set, $this->_update,
        $this->_where);
  }
}
