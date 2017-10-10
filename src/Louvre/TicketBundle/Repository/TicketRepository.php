<?php

namespace Louvre\TicketBundle\Repository;


class TicketRepository extends \Doctrine\ORM\EntityRepository
{
    public function findNbTicketsAtDate($date)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('COUNT(a.id)')->where('a.date = :date')->setParameter('date', $date);


        // On n'ajoute pas de critère ou tri particulier, la construction
        // de notre requête est finie

        // On récupère la Query à partir du QueryBuilder
        $query = $qb->getQuery();

        // On récupère les résultats à partir de la Query
        $results = $query->getResult();

        $intResult = intval($results[0][1]);


        // ICI ON PARAMETRE LE NOMBRE DE BILLETS DISPONIBLE PAR JOUR
        $ticketByDay = 20;

        //On calcul le nombre de ticket restant disponible
        $ticketsRemaining = $ticketByDay - $intResult;

        // On retourne ces résultats
        return $ticketsRemaining;
    }
}
