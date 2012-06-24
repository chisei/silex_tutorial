<?php

namespace SilexTutorial;

/**
 * Member class
 */
class Member
{

    const STRETCHCOUNT = 1000;

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function authenticate()
    {
    }

    public function get()
    {
    }

    /**
     * 登録
     * @param   array   $data
     * @return  bool
     */
    public function register(array $data)
    {
        $this->db->beginTransaction();

        $sql = "INSERT INTO member SET
            email = :email,
            password = dummy,
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
        
        $stmt->execute();

        $this->db->commit();
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
