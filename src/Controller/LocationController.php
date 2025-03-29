<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Location;
use App\Form\LocationType;
use App\Entity\Photo;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class LocationController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $em
    ) {}

    // Route pour l'interface web (HTML)
    #[Route('/location/form', name: 'app_location_form')]
    public function new(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... (votre code existant)
            return $this->redirectToRoute('app_location_success');
        }

        return $this->render('location/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // API Endpoints (JSON)

    #[Route('/api/locations', name: 'api_location_create', methods: ['POST'])]
public function create(Request $request): JsonResponse

    {
        try {
            $location = $this->serializer->deserialize(
                $request->getContent(), 
                Location::class, 
                'json'
            );

            $this->em->persist($location);
            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'data' => $location
            ], 201, [], [
                'groups' => ['location:read']
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/locations', name: 'api_location_list', methods: ['GET'])]
    public function list(LocationRepository $repo): JsonResponse
    {
        $locations = $repo->findAll();
        
        return $this->json([
            'status' => 'success',
            'data' => $locations
        ], 200, [], [
            'groups' => ['location:read']
        ]);
    }

    #[Route('/api/locations/{id}', name: 'api_location_show', methods: ['GET'])]
    public function show(Location $location): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $location
        ], 200, [], [
            'groups' => ['location:read']
        ]);
    }

    #[Route('/api/locations/{id}', name: 'api_location_update', methods: ['PUT', 'PATCH'])]
    public function update(Location $location, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Location::class,
                'json',
                ['object_to_populate' => $location]
            );

            $this->em->flush();

            return $this->json([
                'status' => 'success',
                'data' => $location
            ], 200, [], [
                'groups' => ['location:read']
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/api/locations/{id}', name: 'api_location_delete', methods: ['DELETE'])]
    public function delete(Location $location): JsonResponse
    {
        $this->em->remove($location);
        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Location deleted'
        ], 204);
    }

    #[Route('/api/locations/{id}/photos', name: 'api_location_add_photos', methods: ['POST'])]
public function addPhotos(Location $location, Request $request): JsonResponse
{
    try {
        if (!$request->files->has('photos')) {
            throw new \Exception('No photos uploaded');
        }

        $uploadedFiles = $request->files->all()['photos'];
        
        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $filename = uniqid().'.'.$file->guessExtension();
            $file->move($this->getParameter('photos_directory'), $filename);
            
            $photo = new Photo();
            $photo->setChemin($filename);
            $location->addPhoto($photo);
        }

        $this->em->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Photos added successfully',
            'data' => $location
        ], 201, [], [
            'groups' => ['location:read']
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}
    // Success route (HTML)
    #[Route('/location/success', name: 'app_location_success')]
    public function success(): Response
    {
        return $this->render('location/success.html.twig');
    }
}