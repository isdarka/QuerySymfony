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
use Doctrine\ORM\Query\Expr\Andx;

class Query extends QueryBuilder
{
// 	/** @var $metadata Model\Metadata\AbstractMetadata */
// 	protected $metadata;
	
// 	/** @var $adapter Zend\Db\Adapter\Adapter */
// 	private $adapter;
	
	/** @var $entityName string */
	private $entityName;
	
// 	private $predicate = null;
	
// 	/** @var $where Zend\Db\Sql\Where */
// 	protected $where;

	private $query;
	private $columns = array();
	
	const SQL_STAR = '*';
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
	
// 	/**
// 	 * Add WHERE AND clausule
// 	 * @param unknown $field
// 	 * @param unknown $value
// 	 * @param unknown $comparision
// 	 * @return \Query\Query
// 	 */
// 	public function whereAdd($field, $value, $comparision = self::EQUAL)
// 	{
// 		$this->predicate($field, $value,$comparision, Predicate::COMBINED_BY_AND);
// 		return $this;
// 	}
	
// 	/**
// 	 * Add WHERE OR clausule
// 	 * @param unknown $field
// 	 * @param unknown $value
// 	 * @param unknown $comparision
// 	 * @return \Query\Query
// 	 */
// 	public function whereOrAdd($field, $value, $comparision = self::EQUAL)
// 	{
// 		$this->predicate($field, $value,$comparision, Predicate::COMBINED_BY_OR);
// 		return $this;
// 	}
	
// 	/**
// 	 * 
// 	 * @param unknown $field
// 	 * @param unknown $value
// 	 * @param unknown $comparision
// 	 * @param unknown $combination
// 	 * @throws QueryException
// 	 */
// 	private function predicate($field, $value, $comparision, $combination)
// 	{
// 		if($combination == Predicate::COMBINED_BY_AND)
// 			$this->predicate = new Predicate(null, Predicate::COMBINED_BY_AND);
// 		else
// 			$this->predicate->__get("or");
	
// 		switch ($comparision)
// 		{
// 			case self::IN:
// 				if(!is_array($value))
// 					throw new QueryException('$value must be array but is '.gettype($value));
// 				$this->predicate->in($this->entityName . "." . $field, $value);
// 				break;
// 			case self::EQUAL:
// 				$this->predicate->equalTo($this->entityName . "." . $field, $value);
// 				break;
// 			case self::BETWEEN:
// 				if(!is_array($value))
// 					throw new QueryException('$value must be array but is '.gettype($value));
// 				$this->predicate->between($field, $value[0], $value[1]);
// 				break;
// 			default:
// 				$this->predicate->equalTo($field, $value, $comparision);
// 				break;
// 		}
// 		if($combination == Predicate::COMBINED_BY_AND)
// 			$this->where->addPredicate($this->predicate);
	
// 	}
	
	
// 	/**
// 	 * Specify columns from which to select
// 	 * @param unknown $field
// 	 * @param string $alias
// 	 * @param string $mutator
// 	 * @return \Query\Query
// 	 */
	public function addColumn($field, $alias = null, $mutator = null)
	{
// 		if(!empty($this->columns))
// 			$this->columns = array();
		
		if($field instanceof Query)
		{
			$this->select(new Expression(sprintf(("(%s)"), $field->toSql())));
		}else{
			if(is_null($mutator) && !is_null($alias))
				$this->columns[$alias] = $field;
			elseif (is_null($alias))
			$this->columns[$field] = $field;
			else
				$this->columns[$alias] = new Expression(sprintf($mutator, $field));
		}
		return $this;
	}
	
// 	/**
// 	 * Add Group By field
// 	 * @param string $field
// 	 */
// 	public function addGroupBy($field)
// 	{
// 		$this->group($field);
// 	}
	
// 	/**
// 	 * Order descendent data by field
// 	 * @param string $field
// 	 */
// 	public function addDescendingOrderBy($field)
// 	{
// 		$this->order[$field] = self::DESC;
// 	}
	
// 	/**
// 	 * Order ascendent data by field
// 	 * @param string $field
// 	 */
// 	public function addAscendingOrderBy($field)
// 	{
// 		$this->order[$field] = self::ASC;
// 	}
	
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
	
	private function getConnection()
	{
		return $this->getEntityManager()->getConnection();
	}
	
	private function getStatement()
	{
		return $this->getConnection()->executeQuery($this);
	}
	
	
// 	public function innerJoin(AbstractBean $bean)
// 	{
// 		$this->join(
// 					array($this->getBeanMetadata($bean)->getEntityName() => $this->getBeanMetadata($bean)->getTableName()),
// 					$this->metadata->getEntityName().".".$this->getBeanMetadata($bean)->getPrimaryKey().
// 					"=".
// 					$this->getBeanMetadata($bean)->getEntityName().
// 					".".
// 					$this->getBeanMetadata($bean)->getPrimaryKey(),
// 					self::SQL_STAR,
// 					self::JOIN_INNER
// 				);
// 		return $this;
// 	}
	
// 	private function getBeanMetadata(AbstractBean $bean)
// 	{
// 		$bean = explode('\\', get_class($bean));
// 		$metadata = substr($bean[3], 0, -4)."Metadata";
// 		$bean[2] = "Metadata";
// 		$bean[3] = $metadata;
// 		$metadata = implode('\\', $bean);

// 		if(!class_exists($metadata))
// 			throw new QueryException($metadata.' not found');
		
// 		return new $metadata();
		
// 	}
	
	
	
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
	 * @return AbstractBean
	 */
	public function findOne()
	{
		$array = $this->fetchOne();
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
	
// 	public function findByPkOrThrow($primaryKey, $exception)
// 	{
// 		if($this->findByPk($primaryKey) instanceof AbstractBean == false)
// 			throw new QueryException($exception);
// 		else 
// 			return $this->findByPk($primaryKey);
// 	}
}