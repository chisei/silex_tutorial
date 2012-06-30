<?php

namespace SilexTutorial\Service;

/**
 * Member class
 */
class Member extends \ArrayObject
{

    const STRETCHCOUNT = 1000;

    protected $db;

    private $data = array();

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function set($data)
    {
        foreach($data as $key => $value)
        {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * 認証
     * @param   string  $email
     * @param   string  $password
     * @return  bool
     */
    public function authenticate($email, $password)
    {
        $member = $this->getByEmail($email);

        if($member['password'] === $this->passwordHash($member['id'], $password))
        {
            return true;
        }

        return false;
    }


    /**
     * 登録
     * @param   array   $data
     * @return  bool
     */
    public function register(array $data)
    {
        $result = false;

        $this->db->beginTransaction();

        try
        {
            $sql = "INSERT INTO member SET
                email = :email,
                password = 'dummy',
                created_at = now()";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();

            $id = $this->db->lastInsertId();

            $sql = "UPDATE member SET
                password = :password,
                updated_at = now()
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $this->passwordHash($id, $data['password']));
            $stmt->bindParam('id', $id);
            
            $result = $stmt->execute();
        }
        catch(Exception $e)
        {
            $this->db->rollback();
            throw $e;
        }

        $this->db->commit();

        $this->data = $this->getById($id);

        return $result;
    }

    /**
     * @param   array   $data
     * @return  bool
     */
    public function edit(array $data)
    {
        $this->db->beginTransaction();

        try
        {
            if(!isset($this->data['id']))
            {
                return false;
            }

            if(isset($data['password']))
            {
                $data['password'] = $this->passwordHash($this->data['id'], $data['password']);
            }

            $sql = "UPDATE member SET ";

            foreach($data as $columnName => $value)
            {
                $sql .= " `$columnName` = ? ";
            }
            $sql .= " WHERE id = ?";

            $affectedRowsCount = $this->db->update(
                'member',
                $data,
                array('id' => $this->data['id'])
            );
            
        }
        catch(Exception $e)
        {
            $this->db->rollback();
            throw $e;
        }

        $this->db->commit();

        // データ入れ替え
        $this->set($data);

        return $affectedRowsCount ? true : false;
    }

    /**
     * id から取得
     * @param   int $id
     * @return  array
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM member WHERE id = ?";

        if(!$this->data)
        {
            $this->data = $this->db->fetchAssoc($sql, array((int) $id)) ?: array();
        }

        return $this->data;
    }

    /**
     * emailから取得
     * @param   string  $email
     * @return  $this
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM member WHERE email = ?";

        if(!$this->data)
        {
            $this->data = $this->db->fetchAssoc($sql, array($email)) ?: array();
        }

        return $this;
    }


    /**
     * @param   int $id
     * @return  string  $salt
     */
    protected function getSalt($id)
    {
        return md5($id);
    }

    /**
     * @param   int  $id
     * @param   string  $password
     */
    protected function passwordHash($id, $password)
    {
        $salt = $this->getSalt($id);
        $hash = '';
        for($i = 0; $i < self::STRETCHCOUNT; $i++)
        {
            $hash = hash('sha256', $hash . $password . $salt);
        }

        return $hash;
    }

}
