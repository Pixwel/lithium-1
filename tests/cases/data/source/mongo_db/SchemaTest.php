<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2016, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\data\source\mongo_db;

use MongoId;
use lithium\data\source\MongoDb;
use lithium\data\source\mongo_db\Schema;

class SchemaTest extends \lithium\test\Unit {

	protected $_db;

	public function skip() {
		$this->skipIf(!MongoDb::enabled(), 'MongoDb is not enabled');
	}

	public function setUp() {
		$this->_db = new MongoDb(['autoConnect' => false]);
	}

	public function testCastingIdArray() {
		$schema = new Schema(['fields' => [
			'_id' => ['type' => 'id'],
			'users' => ['type' => 'id', 'array' => true]
		]]);

		$result = $schema->cast(null, null, ['users' => new MongoId()], [
			'database' => $this->_db
		]);

		$this->assertEqual(['users'], array_keys($result->data()));
		$this->assertCount(1, $result->users);
		$this->assertInstanceOf('MongoId', $result->users[0]);
	}

	public function testCastingEmptyValues() {
		$schema = new Schema(['fields' => [
			'_id' => ['type' => 'id'],
			'foo' => ['type' => 'string', 'array' => true]
		]]);
		$result = $schema->cast(null, null, null, ['database' => $this->_db]);
	}
}

?>