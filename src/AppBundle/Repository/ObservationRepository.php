<?php

namespace AppBundle\Repository;

/**
 * ObservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ObservationRepository extends \Doctrine\ORM\EntityRepository
{
    public function myfindAll()
    {
        $query = $this->_em->createQuery('SELECT o FROM AppBundle:Observation o ORDER BY o.waiting DESC, o.date DESC');

        $results = $query->getResult();
        return $results;
    }

    public function getFiltrer($data)
    {
        $query = $this->createQueryBuilder('o')
            ->where('o.dateObservation > :debut')
            ->andWhere('o.valided = :valided')
            ->andWhere('o.dateObservation < :fin')->andWhere('o.taxref IN (:taxref)')
            ->setParameters(array(
                'valided' => true,
                'debut' => $data['debut'],
                'fin' => $data['fin'],
                'taxref' => $data['taxref']
            ));

        $observations = $query->getQuery()->getResult();

        return $observations;
    }

    public function getFilterWithoutTaxref($data)
    {
        $query = $this->createQueryBuilder('o')
            ->where('o.valided = :valided')
            ->andWhere('o.dateObservation > :debut')
            ->andWhere('o.dateObservation < :fin')->setParameters(array(
                'valided' => true,
                'debut' => $data['debut'],
                'fin' => $data['fin']
            ));

        $observations = $query->getQuery()->getResult();

        return $observations;
    }
}
