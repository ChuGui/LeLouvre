<?php

class CalculatePriceTest extends PHPUnit_Framework_TestCase
{

    public function testCalculatePrice()
    {
        $booking = new \Louvre\TicketBundle\Entity\Booking();
        $ticketbebe = new \Louvre\TicketBundle\Entity\Ticket();
        $ticketbebe->setBirthday("10-08-2016");
        $booking1->addTicket($ticketbebe);
        $this->assertEquals(0,\Louvre\TicketBundle\Service\Price\CalculatePrice::totalPriceOf($booking1));

    }

}