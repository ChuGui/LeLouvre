<?php

class CalculatePriceTest extends PHPUnit_Framework_TestCase
{

    public function testCalculatePriceForBaby()
    {
        $calculatePrice = new \Louvre\TicketBundle\Service\Price\CalculatePrice();
        $booking1 = new \Louvre\TicketBundle\Entity\Booking();
        $ticketbaby = new \Louvre\TicketBundle\Entity\Ticket();
        $birthday = new DateTime('2017-01-01');
        $birthday->format("Y-m-d");
        $ticketbaby->setBirthday($birthday);
        $booking1->addTicket($ticketbaby);
        $this->assertEquals(0, $calculatePrice->totalPriceOf($booking1));
    }

    public function testCalculatePriceForAdulte()
    {
        $calculatePrice = new \Louvre\TicketBundle\Service\Price\CalculatePrice();
        $booking1 = new \Louvre\TicketBundle\Entity\Booking();
        $ticketAdulte = new \Louvre\TicketBundle\Entity\Ticket();
        $birthday = new DateTime('1984-01-01');
        $birthday->format("Y-m-d");
        $ticketAdulte->setBirthday($birthday);
        $booking1->addTicket($ticketAdulte);
        $this->assertEquals(16, $calculatePrice->totalPriceOf($booking1));
    }

    public function testCalculatePriceForDiscount()
    {
        $calculatePrice = new \Louvre\TicketBundle\Service\Price\CalculatePrice();
        $booking1 = new \Louvre\TicketBundle\Entity\Booking();
        $ticketDiscount = new \Louvre\TicketBundle\Entity\Ticket();
        $birthday = new DateTime('1984-01-01');
        $birthday->format("Y-m-d");
        $ticketDiscount->setBirthday($birthday);
        $ticketDiscount->setDiscount(true);
        $booking1->addTicket($ticketDiscount);
        $this->assertEquals(10, $calculatePrice->totalPriceOf($booking1));
    }

}