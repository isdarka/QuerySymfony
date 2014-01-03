<?php
/**
 *
 * @author isdarka
 * @created Nov 22, 2013 5:11:59 PM
 */

namespace Query;

use Doctrine\DBAL\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Comparison;
use Query\Interfaces\Comparision;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Model\Bean\AbstractBean;
use Doctrine\ORM\Query\Expr\Join;
use Model\Metadata\AbstractMetadata;

class Query extends QueryBuilder implements Comparision
{
	
	/** @var $entityName string */
	private $entityName;
	
	
	const SQL_STAR = '*';
	
	const COMBINED_BY_AND = "AND";
	const COMBINED_BY_OR = "OR";
	
	/**
	 * 
	 * @param Adapter $adapter
	 * @param unknown $tableName
	 * @param string $entity
	 */
	public function __construct(EntityManager $manager)
	{
		parent::__construct($manager);
		$this->from($this->getMetadata()->getTableName(), $this->getMetadata()->getEntityName());
		$this->select(self::SQL_STAR);
	}
	
	/**
	 * Get SQL string for statement
	 * @return string
	 */
	public function toSql()
	{
		return $this->getQuery()->getDQL();
	}
	
	public function whereAdd($field, $value, $comparision = self::EQUAL)
	{
		$this->predicate($field, $value, $comparision, self::COMBINED_BY_AND);
		return $this;
	}
	
	/**
	 * 
	 * @todo add expr functions
	 * 
	 * @param unknown $field
	 * @param unknown $value
	 * @param unknown $comparision
	 * @param unknown $combination
	 * @throws QueryException
	 */
	private function predicate($field, $value, $comparision, $combination)
	{
		$expr = new Expr();
		if(gettype($value) == "string")
			$value = "'" . $value . "'";
		switch ($comparision)
		{
			case self::EQUAL :
				$expr = $expr->eq($field, $value);
				break;
			case self::IN :
				if(!is_array($value))
					throw new QueryException('$value must be a array');
				$expr = $expr->in($field, $value);
				break;
			case self::IS_NOT_NULL:
				$expr = $expr->isNotNull($field);
				break;
			case self::IS_NULL:
				$expr = $expr->isNull($field);
				break;
		}
		if($combination == self::COMBINED_BY_AND)
			parent::andWhere($expr);
		elseif($combination == self::COMBINED_BY_OR)
			parent::orWhere($expr);
		
	}
	
	public function whereOrAdd($field, $value, $comparision = self::EQUAL)
	{
		$this->predicate($field, $value,$comparision, self::COMBINED_BY_OR);
		return $this;
	}
	
	
	/**
	 * Specify columns from which to select
	 * @param string $field
	 * @param string $alias
	 * @param string $mutator
	 * @return \Query\Query
	 */
	public function addColumn($field, $alias = null, $mutator = null)
	{
		$this->_type = self::SELECT;
		
		if (empty($field)) {
			return $this;
		}
		
		if(reset($this->_dqlParts["select"][0]->getParts()) == self::SQL_STAR)
			$this->_dqlParts["select"] = array();
		
		$selects = is_array($field) ? $field : func_get_args();
		$this->add('select', new Expr\Select($selects), true);
		return $this;
	}
	
	/**
	 * Add Group By field
	 * @param string $field
	 */
	public function addGroupBy($field)
	{
		parent::addGroupBy($field);
		return $this;
	}
	
	/**
	 * Order descendent data by field
	 * @param string $field
	 */
	public function addDescendingOrderBy($field)
	{
		parent::orderBy($field, self::DESC);
		return $this;
	}
	
	/**
	 * Order ascendent data by field
	 * @param string $field
	 */
	public function addAscendingOrderBy($field)
	{
		parent::orderBy($field, self::ASC);
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function fecthAll()
	{
		return $this->getStatement()->fetchAll();
	}
	
	/**
	 * 
	 * @return mixed
	 */
	public function fetchOne()
	{
		return $this->getStatement()->fetch();
	}
	
	/**
	 * Return count of rowsCount elements of an object
	 * @return number
	 */
	public function count()
	{
		return (int) $this->getStatement()->rowCount();
	}
	
	/**
	 * 
	 * @return \Doctrine\DBAL\Connection
	 */
	private function getConnection()
	{
		return $this->getEntityManager()->getConnection();
	}
	
	/**
	 * 
	 * @return Ambigous <\Doctrine\DBAL\Driver\Statement, \Doctrine\DBAL\Driver\ResultStatement, \Doctrine\DBAL\Cache\ResultCacheStatement>
	 */
	private function getStatement()
	{
		return $this->getConnection()->executeQuery($this);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Doctrine\ORM\QueryBuilder::innerJoin()
	 */
	public function innerJoin(AbstractBean $bean)
	{
		$exp = $this->expr()->eq(
				$this->getBeanMetadata($bean)->getEntityName() . "." . $this->getBeanMetadata($bean)->getPrimaryKey(), 
				$this->getMetadata()->getEntityName() . "." . $this->getBeanMetadata($bean)->getPrimaryKey()
		);
		parent::innerJoin(
				$this->getBeanMetadata($bean)->getTableName(), 
				$this->getBeanMetadata($bean)->getEntityName(),
				Join::ON,
				$exp
		);
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Doctrine\ORM\QueryBuilder::leftJoin()
	 */
	public function leftJoin(AbstractBean $bean)
	{
		$exp = $this->expr()->eq(
				$this->getBeanMetadata($bean)->getEntityName() . "." . $this->getBeanMetadata($bean)->getPrimaryKey(),
				$this->getMetadata()->getEntityName() . "." . $this->getBeanMetadata($bean)->getPrimaryKey()
		);
		parent::leftJoin(
				$this->getBeanMetadata($bean)->getTableName(),
				$this->getBeanMetadata($bean)->getEntityName(),
				Join::ON,
				$exp
		);
		
		return $this;
	}
	/**
	 * 
	 * @param AbstractBean $bean
	 * @throws QueryException
	 * @return AbstractMetadata
	 */
	private function getBeanMetadata(AbstractBean $bean)
	{
		$bean = str_replace("Bean", "Metadata", get_class($bean));
		$beanMetadata = $bean . "Metadata";
		if(!class_exists($beanMetadata))
			throw new QueryException($beanMetadata.' not found');
		
		return new $beanMetadata();
		
	}
	
	
	
	/**
	 *
	 * @return AbstractCollection
	 */
	public function find()
	{
		$array = $this->fecthAll();
		$collection = $this->getMetadata()->newCollection();
		foreach ($array as $item)
			$collection->append($this->getMetadata()->getFactory()->createFromArray($item));
	
		return $collection;
	}
	
	/**
	 * 
	 * @return NULL|AbstractBean
	 */
	public function findOne()
	{
		$array = $this->fetchOne();
		if(!$array)
			return null;
		return $this->getMetadata()->getFactory()->createFromArray($array);
	}
	
	/**
	 * 
	 * @param int $primaryKey
	 */
	public function findByPk($primaryKey)
	{
		if(is_null($primaryKey) || empty($primaryKey))
			throw new QueryException("Primary key not defined");
		$where = new Andx();
		$where->add($this->expr()->eq($this->getMetadata()->getEntityName() . "." .$this->getMetadata()->getPrimaryKey(), $primaryKey));
		$this->where($where);
		return $this->findOne();
	}
	
	/**
	 * 
	 * @param unknown $primaryKey
	 * @param unknown $exception
	 * @throws QueryException
	 * @return Ambigous <NULL, \Model\Bean\AbstractBean>
	 */
	public function findByPkOrThrow($primaryKey, $exception)
	{
		if($this->findByPk($primaryKey) instanceof AbstractBean == false)
			throw new QueryException($exception);
		else 
			return $this->findByPk($primaryKey);
	}
}