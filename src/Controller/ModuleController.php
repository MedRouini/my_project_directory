<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Patient;
use App\Repository\ModuleRepository;
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

#[Route('/module', name: 'module_')]
class ModuleController extends AbstractController
{
    private $entityManager;
    private $moduleRepository;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModuleRepository $moduleRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->moduleRepository = $moduleRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/', name: 'show')]
    public function getAllModules(): Response
    {
        $modules = $this->moduleRepository->findAll();

        if (!$modules) {
            return new Response('No Modules found', Response::HTTP_NOT_FOUND);
        }

        return $this->render('module/show.html.twig', [
          'modules' => $modules,
      ]);
    }

    #[Route('/new', name: 'new', priority: 2)]
    public function createModule(Request $request): Response
    {

        $module = new Module();
    
        $form = $this->createFormBuilder($module)
            ->add('name', TextType::class)
            ->add('lastName', TextType::class)
            ->add('cardNumber', TextType::class)
            ->add('birthdate', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('email', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Module'])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
          $this->entityManager->persist($module);
          $this->entityManager->flush();

          return new RedirectResponse($this->generateUrl('module_show', ['id' => $module->getId()]));
        }
    
        return $this->render('module/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}