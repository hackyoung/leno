<?php
define('TEST_MVC', false);
require __DIR__ . '/boot.php';

use \Leno\Database\Row\Creator as RowCreator;
use \Leno\Database\Row\Updator as RowUpdator;
use \Leno\Database\Row\Deletor as RowDeletor;
use \Leno\Database\Row\Selector as RowSelector;
use \Leno\Database\Expr;

/*
    CREATE TABLE user_test (
        id CHAR(36) NOT NULL,
        name VARCHAR(32) NOT NULL,
        age int NOT NULL
    );

    CREATE TABLE book (
        id CHAR(36) NOT NULL,
        name VARCHAR(64) NOT NULL,
        published DATETIME NOT NULL,
        author CHAR(36) NOT NULL
    );

 */
class TestTable extends \Leno\ORM\Entity
{
    protected static $table = 'test_table';

    public static $primary = 'ids';

    protected static $attributes = [
        'ids' => ['type' => 'array']
    ];
}

// $test_table = new TestTable;
// $test_table->addIds('1')
//     ->addIds('2')
//     ->addIds('3')
//     ->save();
// $test_table = new TestTable;
// $test_table->setIds(['4', '5'])
//     ->save();
$result = TestTable::selector()
    ->byIdsNotInclude('3')
    ->find();

var_dump($result);
/*
(new RowCreator('user_test'))
      ->setId(uuid())
      ->setName('hackyoung')
      ->setAge(24)
    ->newRow()
      ->setId(uuid())
      ->setName('young')
      ->setAge(13)
    ->create();

(new RowCreator('book'))
      ->setId(uuid())
      ->setName('php从入门到放弃')
      ->setAuthor('2a3c4c5f-e6d1-b9e8-87ff-d09bf891d200')
      ->setPublished('2016-04-05')
    ->newRow()
      ->setId(uuid())
      ->setName('JAVASCRIPT从入门到放弃')
      ->setAuthor('2a3c4c5f-e6d1-b9e8-87ff-d09bf891d200')
      ->setPublished('2016-04-04')
    ->create();
 */
/*
$ret = (new RowSelector('user_test'))->field([
    'name' => 'user_name',
    'age' => 'user_age',
    'id'
])->execute()->fetchAll();
 */
// $ret = (new RowSelector('user_test'))
//     ->fieldName('user_name')
//     ->fieldAge('user_age')
//     ->fieldId()
//     ->byExpr(new Expr('id = hello'))
//     ->execute()->fetchAll();
/*
$ret = (new RowSelector('user_test'))
    ->field('name', 'user_name')
    ->fieldAge('age', 'user_age')
    ->fieldId()
    ->execute()->fetchAll();
 */
/*
$ret = (new RowSelector('user_test'))
    ->fieldName('user_name')
    ->fieldAge('user_age')
    ->fieldId()
    ->byEqAge(24)
    ->byLikeName('young')
    ->execute()->fetchAll();
 */
/*
$book_selector = (new RowSelector('book'));

$user_selector = (new RowSelector('user_test'))
    ->fieldName('user_name')
    ->fieldAge('user_age')
    ->byEqAge(24);

$ret = $book_selector->join( $user_selector->onEqId(
    $book_selector->getFieldExpr('author')
))->execute()->fetchAll();

var_dump($ret);
 */
/*
(new RowUpdator('user_test'))->setName('hahayoung')
    ->byEqName('hackyoung')
    ->update();
 */
/*
(new RowDeletor('user_test'))->byEqName('young')->delete();
 */
