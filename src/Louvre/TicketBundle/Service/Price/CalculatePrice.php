<?php

namespace Louvre\TicketBundle\Service\Price;

class CalculatePrice
{
    public function totalPriceOf($booking)
    {
        //Récupération des tickets
        $tickets = $booking->getTickets();

        //Calcul du prix de chaque ticket en fonction des entrées client
        $totalPrice = 0;

        foreach ($tickets as $ticket) {
            $ticketDate = $booking->getVisitingDay();

            //Les tickets prennent la date de bookingSession
            $ticket->setDate($ticketDate);

            //Calcul de l'âge du client
            $birthday = $ticket->getBirthday();
            $today = new \Datetime();
            $interval = date_diff($birthday, $today);
            $age = $interval->y;

            //Calcul du prix grâce à l'âge et aux réductions
            if ($age < 4) {
                $ticket->setPrice(0);
            } else if ($age >= 4 && $age <= 12) {
                $ticket->setPrice(8);
            } else if ($age > 59) {
                $ticket->setPrice(12);
            } else {
                if ($ticket->getDiscount() == true) {
                    $ticket->setPrice(10);
                } else {
                    $ticket->setPrice(16);
                }
            }

            //Incrémentation de $totalprice pour le calcul du prix total
            $ticketPrice = $ticket->getPrice();
            $totalPrice = $totalPrice + $ticketPrice;

        }


        //Le prix est divisé par 2 si la case pour la demi journée est  cochée
        $halfdayBookingSession = $booking->getHalfday();
        if ($halfdayBookingSession == true)
        {
            $totalPrice = $totalPrice / 2;
        }
        $booking->setTotalPrice($totalPrice);

        return $totalPrice;
    }
}