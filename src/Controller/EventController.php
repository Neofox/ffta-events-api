<?php

namespace App\Controller;

use App\Service\Geocoding;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    private Geocoding $geocoding;

    public function __construct(Geocoding $geocoding)
    {
        $this->geocoding = $geocoding;
    }


    #[Route('/event', name: 'app_event')]
    public function index(): Response
    {
        return $this->json($this->geocoding->getGeolocationFromAddress('AVENUE CHAMPLAIN 9440 CHENNEVIERES SUR MARNE'));

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EventController.php',
        ]);
    }
}
