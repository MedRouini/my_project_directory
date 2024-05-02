<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormInterface;

#[Route('/patient', name: 'patient_')]
class PatientController extends AbstractController
{
    private $entityManager;
    private $patientRepository;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        PatientRepository $patientRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->patientRepository = $patientRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/{id}', name: 'show')]
    public function getPatient(int $id): Response
    {
        $patient = $this->patientRepository->find($id);

        if (!$patient) {
            return new Response('Patient not found', Response::HTTP_NOT_FOUND);
        }

        return $this->render('patient/show.html.twig', [
          'patient' => $patient,
      ]);
    }

    #[Route('/new', name: 'new', priority: 2)]
    public function createPatient(Request $request): Response
    {

        $patient = new Patient();
    
        $form = $this->createFormBuilder($patient)
            ->add('name', TextType::class)
            ->add('lastName', TextType::class)
            ->add('cardNumber', TextType::class)
            ->add('birthdate', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('email', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Patient'])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
          $this->entityManager->persist($patient);
          $this->entityManager->flush();

          return new RedirectResponse($this->generateUrl('patient_show', ['id' => $patient->getId()]));
        }
    
        return $this->render('patient/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function updatePatient(int $id, Request $request): Response
    {
        $patient = $this->patientRepository->find($id);

        if (!$patient) {
            return new Response('Patient not found', Response::HTTP_NOT_FOUND);
        }

        $updatedPatient = $this->serializer->deserialize($request->getContent(), Patient::class, 'json');

        if ($updatedPatient->getCardNumber()) {
            $patient->setCardNumber($updatedPatient->getCardNumber());
        }

        // Repeat the above if block for each field that can be updated

        $this->entityManager->flush();

        return new Response('Patient updated', Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deletePatient(int $id): Response
    {
        $patient = $this->patientRepository->find($id);

        if (!$patient) {
            return new Response('Patient not found', Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($patient);
        $this->entityManager->flush();

        return new Response('Patient deleted', Response::HTTP_NO_CONTENT);
    }
}