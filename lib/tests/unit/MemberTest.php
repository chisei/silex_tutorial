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

    public function setUp()
    {
        $this->db->beginTransaction();
    }

    public function tearDown()
    {
        $this->db->rollback();
    }

    /**
     * 会員登録のテスト
     */
    public function testRegister()
    {
        // テストデータ登録
        $result = $this->createMember();

        $this->assertTrue($result, '登録成功');
        $this->assertGreaterThan(0, count($this->member->count()), 'データ取得成功');

    }

    /**
     * 会員登録失敗のテスト
     */
    public function testFailureToRegister()
    {
        try
        {
            $this->member->register(array('email' => '', 'password' => ''));
        }
        catch(Exception $e)
        {
        }
    }

    /**
     * 認証のテスト
     */
    public function testAuthenticacte()
    {
        // テストデータ登録
        $this->createMember();

        $this->assertTrue($this->member->authenticate('test@example.com', '1234'), '認証成功');
        $this->assertFalse($this->member->authenticate('test@example.com', ''), '認証失敗');
    }

    /**
     * Email編集テスト
     */
    public function testEmailEdit()
    {
        // テストデータ作成
        $this->createMember();

        // メール変更
        $this->member->edit(array('email'=>'test1@example.com'));
    }

    /**
     * パスワード編集のテスト
     */
    public function testPasswordEdit()
    {
        $this->createMember();

        $this->member->edit(array('password' => '12345'));
    }

    /**
     * @return  bool    $result
     */
    protected function createMember()
    {
        // テストデータ登録
        $result = $this->member->register(array('email' => 'test@example.com', 'password' => '1234'));

        return $result;
    }
}
