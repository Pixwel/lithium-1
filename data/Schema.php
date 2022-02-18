<?php
/**
 * li₃: the most RAD framework for PHP (http://li3.me)
 *
 * Copyright 2011, Union of RAD. All rights reserved. This source
 * code is distributed under the terms of the BSD 3-Clause License.
 * The full license text can be found in the LICENSE.txt file.
 */

namespace lithium\data;

use RuntimeException;

/**
 * This class encapsulates a schema definition, usually for a model class, and is comprised
 * of named fields and types.
 */
class Schema extends \lithium\core\ObjectDeprecated implements \ArrayAccess {

	protected $_fields = [];

	protected $_meta = [];

	protected $_locked = false;

	protected $_types = [];

	protected $_autoConfig = ['fields', 'meta', 'locked', 'types'];

	/**
	 * Constructor.
	 *
	 * @param array $config Available configuration options are:
	 *        - `'fields'` _array_
	 * @return void
	 */
	public function __construct(array $config = []) {
		$defaults = ['fields' => []];
		parent::__construct($config + $defaults);
	}

	protected function _init() {
		parent::_init();

		foreach ($this->_fields as $key => $type) {
			if (is_string($type)) {
				$this->_fields[$key] = compact('type');
				continue;
			}
			if (isset($this->_fields[$key][0]) && !isset($this->_fields[$key]['type'])) {
				$this->_fields[$key]['type'] = $this->_fields[$key][0];
				unset($this->_fields[$key][0]);
			}
		}
	}

	public function fields($name = null, $key = null) {
		if (!$name) {
			return $this->_fields;
		}
		$field = isset($this->_fields[$name]) ? $this->_fields[$name] : null;

		if ($field && $key) {
			return isset($field[$key]) ? $field[$key] : null;
		}
		return $field;
	}

	public function names() {
		return array_keys($this->_fields);
	}

	public function defaults($name = null) {
		if ($name) {
			if (isset($this->_fields[$name]['default'])) {
				return $this->_fields[$name]['default'];
			}
			return null;
		}
		$defaults = [];

		foreach ($this->_fields as $key => $value) {
			if (isset($value['default'])) {
				$defaults[$key] = $value['default'];
			}
		}
		return $defaults;
	}

	public function meta($name = null) {
		if (!$name) {
			return $this->_meta;
		}
		return isset($this->_meta[$name]) ? $this->_meta[$name] : null;
	}

	public function has($field) {
		if (is_string($field)) {
			return isset($this->_fields[$field]);
		}
		if (is_array($field)) {
			return array_intersect($field, array_keys($this->_fields)) == $field;
		}
	}

	/**
	 * Detects properties of a field, i.e. if it supports arrays.
	 *
	 * @param string $condition
	 * @param string $field
	 * @return boolean
	 */
	public function is($condition, $field) {
		if (!isset($this->_fields[$field])) {
			return null;
		}
		return isset($this->_fields[$field][$condition]) && $this->_fields[$field][$condition];
	}

	public function type($field) {
		if (!isset($this->_fields[$field]['type'])) {
			return null;
		}
		$type = $this->_fields[$field]['type'];
		return isset($this->_types[$type]) ? $this->_types[$type] : $type;
	}

	public function cast($object, $key, $data, array $options = []) {
		return $data;
	}

	public function reset() {
		$this->_fields = [];
	}

	/**
	 * Appends additional fields to the schema. Will not overwrite existing fields if any conflicts
	 * arise.
	 *
	 * @param array $fields New schema data.
	 * @return void
	 */
	public function append(array $fields) {
		if ($this->_locked) {
			throw new RuntimeException("Schema cannot be modified.");
		}
		$this->_fields += $fields;
	}

	/**
	 * Merges another `Schema` object into the current one.
	 *
	 * @param object $schema Another `Schema` class object to be merged into the current one.
	 *               If this schema contains field names that conflict with existing field names,
	 *               the existing fields will not be overwritten.
	 * @return void
	 */
	public function merge($schema) {
		if ($this->_locked) {
			throw new RuntimeException("Schema cannot be modified.");
		}
		$this->_fields += $schema->fields();
	}

	public function offsetGet($key): mixed {
		return $this->fields($key);
	}

	public function offsetSet($key, $value): void {
		if ($this->_locked) {
			throw new RuntimeException("Schema cannot be modified.");
		}
		$this->_fields[$key] = $value;
	}

	public function offsetExists($key): bool {
		return isset($this->_fields[$key]);
	}

	public function offsetUnset($key): void {
		unset($this->_fields[$key]);
	}
}

?>