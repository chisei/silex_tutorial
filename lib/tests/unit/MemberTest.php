<?php

use SilexTutorial\Service\Member;

class MemberTest extends PHPUnit_Framework_TestCase
{
    protected $db;
    protected $member;

    public function __construct()
    {
        $app = new Silex\Application();
        $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_mysql',
                'dbname'   => 'silex_tutorial',
                'host'     => 'localhost',
                'user'     => 'silex',
                'password' => 'tutorial'
            ),
        ));

        $this->db = $app['db'];
        $this->member = new Member($this->db);

    }

    /**
     * 会員登録のテスト
     */
    public function testRegister()
    {
        $this->db->beginTransaction();

        // テストデータ登録
        $result = $this->member->register(array('email' => 'test@example.com', 'password' => '1234'));

        $this->assertTrue($result, '登録成功');
        $this->assertGreaterThan(0, count($this->member->get()), 'データ取得成功');

        $this->db->rollback();
    }

    /**
     * 認証のテスト
     */
    public function testAuthenticacte()
    {
        $this->db->beginTransaction();

        // テストデータ登録
        $this->member->register(array('email' => 'test@example.com', 'password' => '1234'));

        $this->assertTrue($this->member->authenticate('test@example.com', '1234'), '認証成功');
        $this->assertFalse($this->member->authenticate('test@example.com', ''), '認証失敗');

        $this->db->rollback();
    }
}
