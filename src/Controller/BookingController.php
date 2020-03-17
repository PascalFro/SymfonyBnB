<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     *
     */
    public function book(Ad $ad, Request $request, EntityManagerInterface $manager)
    {

      $booking = new Booking;
      $form = $this->createForm(BookingType::Class, $booking);

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) {
        $user = $this->getUser();

        $booking->setBooker($user)
                ->setAd($ad);

        // Si les dates ne sont pas dispon ibles, message d'erreur
         if(!$booking->isBookableDates()) {
          $this->addFlash(
            'warning', "Les dates que vous avez choisies ne peuvent être réservées, elles sont déjà prises"
          );
         } else {
                  // Sinon, enregistrement et redirection

        $manager->persist($booking);
        $manager->flush();

        return $this->redirectToRoute('booking_show', ['id' => $booking->getId(), 'withAlert' => true]);
      }
    }



      return $this->render('booking/book.html.twig', [
          'ad' => $ad,
          'form' => $form->createView()
      ]);
  }

/**
 * Permet d'afficher la page d'une réservation
 *
 * @Route("/booking/{id}", name="booking_show")
 *
 * @param  Booking $booking
 * @return Response
 */
  public function show(Booking $booking) {
    return $this->render('booking/show.html.twig', [
      'booking' => $booking
    ]);
  }
}
