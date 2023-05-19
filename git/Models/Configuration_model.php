<?php
namespace App\Models;

use CodeIgniter\Model;

final class Configuration_model extends Model
{
    public function getConfigurations()
    {
        $result = $this->db->query("SELECT * FROM public.winconfig WHERE id > 0")->getResultArray();

        $configurations = [];

        foreach ($result as $row)
        {
            $configurations[$row['id']] = [
                'id' => $row['id'],
                'ename' => $row['ename'],
                'val' => $row['val'],
                'note' => $row['note'],
                'created' => $row['created']
            ];
        }

        return $configurations;
    }

    public function getConfiguration($id)
    {
        $query = "
        SELECT ename, val, note
        FROM public.winconfig
        WHERE id = $id
        ";

        return $this->db->query($query)->getRowArray();
    }

    public function setConfiguration($data, $id = null)
    {
        if (is_null($id))
        {
            $this->db->table('public.winconfig')->insert($data);

            $id = $this->db->insertID();
        }
        else
        {
            $this->db->table('public.winconfig')->where('id', $id)->update($data);
        }

        return $id;
    }

    public function unsetConfiguration($id)
    {
        $result = $this->db->query("DELETE FROM public.winconfig WHERE id = {$id}");

        return $result;
    }
}