<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    const STATUSES = [
        1 => 'New',
        2 => 'In progress',
        3 => 'Done'
    ];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllWithLongestComment()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT
              t.id as id,
              t.name as name,
              t.description as description,
              t.status as status,
              (SELECT c.text
              FROM comment c
              WHERE t.id = c.task_id
              ORDER BY LENGTH(c.text) DESC
              LIMIT 1) as comment 
            FROM task t';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['price' => 1000]);
        $result = $stmt->fetchAll();
        foreach ($result as &$item) {
            $item['status'] = self::STATUSES[$item['status']];
        }

        return $result;
    }
}
