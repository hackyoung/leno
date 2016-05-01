<?php
namespace Leno\DataMapper;

class Mapper
{
	/**
	 * @var [
	 *	  'name' => ['type' => 'string', 'extra' => ['max_length' => 2015]],
	 *	  'age' => ['type' => 'integer', 'allow_empty' => true,],
	 * ];
	 */
	public static $attributes = [];

	/**
	 * @var [
	 *	  'id', 'name'
	 * ]
	 */
	public static $unique = [];

	public static $primary;

	/**
	 * @var [
	 *		methodName: [
	 *			relation: [],
	 *			class:
	 *		]
	 * ]
	 */
	public static $foreign =[];

	public static $table;

	protected $fresh = true;

	/**
	 * @var [
	 *		city : [obj]
	 * ]
	 */
	protected $relation = [];

	protected $data;

	public function __construct($data = [])
	{
		$class = get_called_class();
		$this->data = new Data($data, $class::$attributes);
	}

	public function __call($method, $parameters = null) {
		$series = array_filter(explode('_', unCamelCase($method, '_')));
		if(!isset($series[0])) {
			throw new \Exception(get_class() .'::'.$method . ' Not Defined');
		}
		switch($series[0]) {
			case 'set':
				array_splice($series, 0, 1);
				$field = implode('_', $series);
				return $this->set($field, $parameters[0]);
			case 'get':
				array_splice($series, 0, 1);
				$field = implode('_', $series);
				return $this->get($field);
			case 'add':
				array_splice($series, 0, 1);
				$field = implode('_', $series);
				return $this->add($field, $parameters[0]);
		}
		throw new \Exception(get_class() .'::'.$method . ' Not Defined');
	}

	public function get($key)
	{
		$data = $this->data;
		if($data->isset($key)) {
			return $data->get($key);
		}
		return $this->getRelateObjs($key);
	}

	public function set($key, $val)
	{
		$foreign = self::getForeign($key);
		if($foreign) {
			$this->relation[$key] = [];
			if(is_array($val)) {
				foreach($val as $val) {
					$this->add($key, $val);
				}
			} else {
				$this->add($key, $val);
			}
			return $this;
		}
		$this->data->set($key, $val);
		return $this;
	}

	public function add($key, $val)
	{
		if(!($foreign = self::getForeign($key))) {
			throw new \Exception('Method Not Allow');
		}
		if(!$val instanceof $foreign['class']) {
			throw new \Exception('Method Not Allow');
		}
		if(!isset($this->relation[$key])) {
			$this->relation[$key] = [];
		}
		$this->relation[$key][] = $val;
		return $this;
	}

	public function isFresh()
	{
		return $this->fresh;
	}

	public function setFresh($fresh = true)
	{
		$this->fresh = $fresh;
		return $this;
	}

	public function id()
	{
		return $this->get(self::getPrimary());
	}

	public function save()
	{
		\Leno\DataMapper\Row::beginTransaction();
		$primary = self::getPrimary();
		if($this->isAutoCreate($primary)) {
			$this->data->set($primary, uuid());
		}
		foreach($this->relation as $key => $obj) {
			$this->saveRelateObjs($key, $obj);
		}
		if(!$this->isFresh() && $this->data->validateAll()) {
			$updator = self::updator();
			$updator->by('eq', $primary, $this->data->get($primary));
			$this->data->each(function($key, $data) use ($updator){
				if($data->isDirty($key)) {
					$updator->set($key, $data->forStore($key));
				}
			});
			return $updator->update();
		}
		$creator = self::creator();
		$this->data->validateAll(function($k, $data) {
			if($this->isAutoCreate($k) && !$data->isset($k)) {
				return false;
			}
		});
		$this->data->each(function($key, $data) use ($creator) {
			$creator->set($key, $data->forStore($key));
		});
		$creator->create();
		return \Leno\DataMapper\Row::commitTransaction();
	}

	protected function saveRelateObjs($key, $objs)
	{
		$foreign = self::getForeign($key);
		if(!$foreign) {
			return;
		}
		$primaryVal = $this->id();
		foreach($objs as $obj) {
			if(!$obj instanceof $foreign['class']) {
				continue;
			}
			$obj->save();
			if(!$foreign['next'] ?? true) {
				$this->data->set($foreign['foreign'], $obj->id());
				continue;
			}
			$next = $foreign['next'];
			if($next['foreign'] !== self::getPrimary()) {
				throw new \Exception ('Foreign Relation Define Error: '.$key);
			}
			$relationClass = $next['class'];
			$relationClass::deletor()
				->by('eq', $next['local'], $primaryVal)
				->by('eq', $foreign['foreign'], $obj->id())
				->delete();
			(new $next['class'])
				->set($foreign['foreign'], $obj->id())
				->set($next['local'], $primaryVal)
				->save();
		}
	}

	protected function getRelateObjs($key) 
	{
		$foreign = self::getForeign($key);
		if(!$foreign) {
			return;
		}
		$theClass = $foreign['class'];
		$selector = $theClass::selector();
		if($foreign['next'] ?? false) {
			$next = $foreign['next'];
			$class = $next['class'];
			$joinSelector = $class::selector()->field(false)
				->on('eq', $foreign['foreign'], $selector->getFieldExpr($foreign['local']))
				->by('eq', $next['local'], $this->get($next['foreign']));
			$selector->join($joinSelector);
		}
		$ret = $selector->find();
		if(count($ret) === 1) {
			return $ret[0];
		}
		return $ret;
	}

	private function isAutoCreate($primary)
	{
		return $primary === self::getPrimary() && 
				!$this->data->isset($primary) && 
				self::getAttribute($primary) === 'uuid';
	}

	public static function getUnique()
	{
		$class = get_called_class();
		return $class::$unique ?? [];
	}

	public static function getPrimary()
	{
		$class = get_called_class();
		return $class::$primary ?? null;
	}

	public static function getForeign($key = null)
	{
		$class = get_called_class();
		if($key) {
			return $class::$foreign ? ($class::$foreign[$key] ?? null) : null;
		}
		return $class::$foreign ?? null;
	}

	public static function getAttribute($key, $inner = 'type')
	{
		if($key  === null) {
			return null;
		}
		$attributes = self::getAttributes();
		return $attributes[$key] ? ($attributes[$key][$inner] ?? null) : null;
	}

	public static function getAttributes()
	{
		$class = get_called_class();
		return $class::$attributes ?? [];
	}

	public static function find($pk)
	{
		$pks = self::getUnique();
		if(!is_array($pk) && count($pks) == 1) {
			$the[$pks[0]] = $pk;
		} else {
			$the = $pk;
		}
		$selector = self::selector();
		foreach($the as $field => $value) {
			$selector->by('eq', $field, $value);
		}
		return $selector->findOne();
	}

	public static function findOrFail($pk)
	{
		$entity = self::find($pk);
		if(!$entity instanceof self) {
			throw new \Exception('Entity Not Found');
		}
		return $entity;
	}

	public static function deletor()
	{
		$class = get_called_class();
		return \Leno\DataMapper\Row::deletor($class::$table)
			->setMapper($class);
	}

	public static function updator()
	{
		$class = get_called_class();
		return \Leno\DataMapper\Row::updator($class::$table)
			->setMapper($class);
	}

	public static function creator()
	{
		$class = get_called_class();
		return \Leno\DataMapper\Row::creator($class::$table)
			->setMapper($class);
	}

	public static function selector()
	{
		$class = get_called_class();
		return \Leno\DataMapper\Row::selector($class::$table)
			->setMapper($class);
	}
}
