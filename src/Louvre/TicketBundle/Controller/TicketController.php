<?php

namespace Louvre\TicketBundle\Controller;

use Louvre\TicketBundle\Entity\Booking;
use Louvre\TicketBundle\Entity\Ticket;
use Louvre\TicketBundle\Form\IdentityType;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Louvre\TicketBundle\Form\BookingType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class TicketController extends Controller
{
    public function homeAction(Request $request)
    {
        /*$nbTicket = $repository->getNbTicket(new DateTime());*/
        $booking = new Booking();
        $ticket = new Ticket();
        $booking->addTicket($ticket);
        $formBooking = $this->createForm(BookingType::class, $booking);
        $formBooking->handleRequest($request);
        //Injection de valeur par défault pour les constraint de doctrine
        //Traitement du formulaire
        if ($formBooking->isSubmitted() && $formBooking->isValid()) {
            //Si la date n'est pas renseigné retour à la page d'accueil
            if ($booking->getVisitingDay() == null) {
                $this->get('session')->getFlashBag()->add('missingDate', 'Vous devez saisir une date de visite.');
                return $this->render('LouvreTicketBundle:Ticket:home.html.twig', array(
                    'formBooking' => $formBooking->createView()
                ));
            }
            //Après 14h les billets passent directement en demi journée pour la date d'aujourd'hui
            $today = date('Y-m-d');
            $time = intval(date('H'));
            $visitingDay = $booking->getVisitingDay();
            $visitingDate = date_format($visitingDay, ('Y-m-d'));
            if (($visitingDate == $today) && ($time > 13)) {
                $booking->setHalfday(true);
            }
            // Vérification de la date et du nombre de tickets disponibles
            $repository = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('LouvreTicketBundle:Ticket');
            $remaining = $repository->findNbTicketsAtDate($booking->getVisitingDay());
            //Récupération du nombre de ticket ajouté par le client
            $newTicketsAdded = count($booking->getTickets());
            $totalTickets = $remaining - $newTicketsAdded;
            //Filtre du nombre de tickets
            if ($totalTickets < 0) {
                $this->get('session')->getFlashBag()
                    ->add('notice', 'Vous avez saisi un trop grand nombre de billets.');
                return $this->render('LouvreTicketBundle:Ticket:home.html.twig', array(
                    'formBooking' => $formBooking->createView(),
                    'remainingTicket' => $remaining
                ));
            } else {
                $bookingSession = new Session();
                $bookingSession->set('booking', $booking);
                return $this->redirectToRoute('louvre_ticket_stripe');
            }
        }
        return $this->render('LouvreTicketBundle:Ticket:home.html.twig', array(
            'formBooking' => $formBooking->createView()
        ));
    }


    public function stripeAction(Request $request)
    {
        //Appel de la session récupération de l'objet booking de type Booking
        $bookingSession = $this->get('session')->get('booking');
        $booking = new Booking();

        //Création du formulaire à partir de l'objet $booking instance de Booking
        $formIdentity = $this->createForm(IdentityType::class, $booking);
        $formIdentity->handleRequest($request);

        // On calcul le prix total en fonction des billets
        $calculatePrice = $this->container->get('louvre.calculatePrice');
        $totalPriceOfBookingSession = $calculatePrice->totalPriceOf($bookingSession);
        $chargeCents = ($bookingSession->getTotalPrice()) * 100;

        //Traitement du formulaire si le formulaire est envoyé
        if ($formIdentity->isSubmitted() && $formIdentity->isValid()) {
            //Request of Stripe
            try {
                Stripe::setApiKey('sk_test_PtV6a44vvIpQAs8dv2OKWNNy');
                $token = $_POST['stripeToken'];
                //Charge the user's card
                $charge = Charge::create(array(
                    "amount" => $chargeCents,
                    "currency" => "eur",
                    "description" => "Achat billet Louvre",
                    "source" => $token,
                ));
            } catch (\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                $err = $body['error'];
                print('Status is:' . $e->getHttpStatus() . "\n");
                print('Type is:' . $err['type'] . "\n");
                print('Code is:' . $err['code'] . "\n");
                // param is '' in this case
                print('Param is:' . $err['param'] . "\n");
                print('Message is:' . $err['message'] . "\n");
            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
            } catch (Exception $e) {
                // Something else happened, completely unrelated to Stripe
            }
            //Récupération de stripe token pour récupérer l'email
            $stripeinfo = \Stripe\Token::retrieve($token);
            $userEmail = $stripeinfo->email;

            //Hydratation manuelle de $booking avec booking session
            $booking->setUrl($userEmail);
            $booking->setLastnameBooking($bookingSession->getLastnameBooking());
            $booking->setFirstnameBooking($bookingSession->getFirstnameBooking());
            $booking->setVisitingDay($bookingSession->getVisitingDay());
            $booking->setTotalPrice($totalPriceOfBookingSession);
            $booking->setHalfDay($bookingSession->getHalfDay());
            $allTickets = $bookingSession->getTickets();
            foreach ($allTickets as $ticket) {
                $booking->addTicket($ticket);
                var_dump($ticket);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($booking);
            $em->flush();
            $message = (new \Swift_Message('Vos billets pour le Louvre'))
                ->setFrom(array('chugustudio@gmail.com' => "Le Louvre"))
                ->setTo($userEmail)
                ->setCharset('utf-8')
                ->setContentType('text/html')
                ->setBody($this->renderView('LouvreTicketBundle:Emails:email.html.twig', array(
                    'allTickets' => $allTickets,
                    'bookingSession' => $bookingSession
                )));
            $this->container->get('mailer')->send($message);
            $request->getSession()->getFlashBag()->add('success', 'Booking à bien enregistré.');
            return $this->redirectToRoute('louvre_ticket_recap');
        }//Fin du traitement du formulaire

        //Rendu de la vu stripe.html.twig
        return $this->render('LouvreTicketBundle:Ticket:stripe.html.twig', array(
            'totalPrice' => $totalPriceOfBookingSession,
            'formIdentity' => $formIdentity->createView()));
    }


    public function recapAction(Request $request)
    {
        return $this->render("LouvreTicketBundle:Ticket:recap.html.twig", array());
    }

    public function ticketsRemainingAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $date = htmlspecialchars($request->query->get('date'));
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('LouvreTicketBundle:Ticket');
            $ticketsRemaining = $repository->findNbTicketsAtDate($date);
            $response = new Response(json_encode($ticketsRemaining));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            return new Response("Erreur");
        }
    }
}