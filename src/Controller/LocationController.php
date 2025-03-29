<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Location;
use App\Form\LocationType;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Persistence\ManagerRegistry;

final class LocationController extends AbstractController
{
    #[Route('/location', name: 'app_location')]
    public function new(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            
            // Gestion des photos
            $photos = $form->get('photos')->getData();
            
            foreach ($photos as $photoFile) {
                if ($photoFile instanceof UploadedFile) {
                    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
                    
                    // Déplacez le fichier dans le répertoire où sont stockées les photos
                    try {
                        $photoFile->move(
                            $this->getParameter('photos_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // Gestion de l'exception
                    }
                    
                    // Créez une nouvelle entité Photo
                    $photo = new Photo();
                    $photo->setChemin($newFilename);
                    $location->addPhoto($photo);
                }
            }
            
            $entityManager->persist($location);
            $entityManager->flush();

            return $this->redirectToRoute('app_location_success');
        }

        return $this->render('location/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/location/success', name: 'app_location_success')]
public function success(): Response
{
    return $this->render('location/success.html.twig');
}
}
