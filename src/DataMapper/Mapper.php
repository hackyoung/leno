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

	protected $data;

	public function __construct($data = [])
	{
		$class = get_called_class();
		$this->data = new Data($data, $class::$attributes);
	}

	public function __call($method, $parameters = null) {
		$series = array_filter(explode('_', unCamelCase($method, '_')));
		if(isset($series[0]) && $series[0] === 'set') {
			array_splice($series, 0, 1);
			$field = implode('_', $series);
			return $this->set($field, $parameters[0]);
		}
		if(isset($series[0]) && $series[0] === 'get') {
			array_splice($series, 0, 1);
			$field = implode('_', $series);
			return $this->get($field);
		}
		throw new \Exception(get_class() .'::'.$method . ' Not Defined');
	}

	public function get($key)
	{
		$data = $this->data;
		if($data->isset($key)) {
			return $data->get($key);
		}
		$class = get_called_class();
		if($class::$foreign[$key]) {
			return;
		}
		$theClass = $class::$foreign[$key]['class'];
		$relation = $class::$foreign[$key]['relation'];
		$selector = $theClass::selector();
		foreach($relation as $local=>$foreign) {
			$selector->by('eq', $foreign, $data->get($local));
		}
		$ret = $selector->find();
		if(count($ret) === 1) {
			return $ret[0];
		}
		return $ret;
	}

	public function set($key, $val)
	{
		$this->data->set($key, $val);
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

	public function save()
	{
		$pks = self::getUnique();
		if(!$this->isFresh()) {
			$this->data->validateAll();
			$updator = self::updator();
			foreach(self::getUnique() as $k) {
				$updator->by('eq', $k, $this->data->get($k));
			}
			$this->data->each(function($key, $data) use ($updator){
				if($data->isDirty($key)) {
					$updator->set($key, $data->forStore($key));
				}
			});
			return $updator->update();
		}
		$creator = self::creator();
		$mapper = $this;
		$this->data->validateAll(function($k, $data) use ($mapper) {
			if($mapper->isAutoCreate($k) && !$data->isset($k)) {
				return false;
			}
		});
		$this->data->each(function($key, $data) use ($creator) {
			$creator->set($key, $data->forStore($key));
		});
		$primary = self::getPrimary();
		if($this->isAutoCreate($primary)) {
			$uuid = uuid();
			$this->data->set($primary, $uuid);
			$creator->set($primary, $uuid);
		}
		return $creator->create();
	}

	public function getUniqueValue()
	{
		$pks_value = [];
		$unique = self::getUnique();
		foreach($unique as $k) {
			$pks_value[$k] = $this->data->get($k);
		}
		return $pks_value;
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

	public static function getAttribute($key, $inner = 'type')
	{
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
		return \Leno\DataMapper\Row::creator($class::$table)
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
