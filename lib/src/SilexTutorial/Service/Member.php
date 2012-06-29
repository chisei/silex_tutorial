<?php

namespace SilexTutorial\Service;

/**
 * Member class
 */
class Member
{

    const STRETCHCOUNT = 1000;

    protected $db;

    protected $data = array();

    public function __construct($db)
    {
        $this->db = $db;
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

        if(isset($member['password']))
        {
            if($member['password'] === $this->passwordHash($member['id'], $password))
            {
                return true;
            }
        }

        return false;
    }

    public function get()
    {
        return $this->data;
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
            if(!isset($data['email']))
            {
                return false;
            }

            $member = $this->getByEmail($data['email']);

            $sql = "UPDATE member SET
                email = ?,
                password = ?
                WHERE email = ?";

            $affectedRowsCount = $this->db->update(
                'member',
                array('email' => $data['email'], 'password' => $this->passwordHash($member['id'], $data['password'])),
                array('email' => $data['email'])
            );
            
        }
        catch(Exception $e)
        {
            $this->db->rollback();
            throw $e;
        }

        $this->db->commit();

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

        if($this->data)
        {
            $this->data = $this->db->fetchAssoc($sql, array((int) $id)) ?: array();
        }

        return $this->data;
    }

    /**
     * emailから取得
     * @param   string  $email
     * @return  array
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM member WHERE email = ?";

        if(!$this->data)
        {
            $this->data = $this->db->fetchAssoc($sql, array($email)) ?: array();
        }

        return $this->data;
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
