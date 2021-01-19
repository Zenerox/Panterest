<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @param PinRepository $pinRepository
     * @return Response
     */
    public function index(PinRepository $pinRepository): Response
    {
        //dd($pinRepository->findAll()); => die dump : affiche les enregistrements présents en bdd sur la page
        $pins = $pinRepository->findBy([], ["createdAt" => "DESC"]);
        return $this->render('pins/index.html.twig', compact('pins'));
        //compact('pins') ==> version abrégée de ['pins' = $pins']
    }

    /**
     * @Route("/pins/create", name="app_pins_create", methods={"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // symfony console debug:autowiring => liste tous les objets qu'on peut injecter
        /* Un formulaire sans 'action' renvoie sur la page en cours, c'est pourquoi on gère ici
            le retour (via la request) du formulaire*/

        $pin = new Pin(); // on instancie un nouveau Pin

        /*$form = $this->createFormBuilder($pin)
            // On indique au formulaire qu'un objet de type Pin est utilisé
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->getForm();*/

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request); // lance la gestion du retour du formulaire si POST

        if($form->isSubmitted() && $form->isValid()) {
            /*$data = $form->getData();
            $pin = new Pin();
            $pin->setTitle($data['title']);
            $pin->setDescription($data['description']);*/

            /* le Pin renvoyé par le formulaire a eu les champs settés donc le code
            ci-dessus est inutile dans ce cas*/
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_show", methods={"GET"})
     * @param Pin $pin
     * @return Response
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit", methods={"GET", "PUT"})
     * @param Pin $pin
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Pin $pin, Request $request, EntityManagerInterface $em): Response
    {
        /*$form = $this->createFormBuilder($pin)
            ->setMethod('PUT')
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->getForm();*/

        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);
        /* le formulaire renvoie un POST de base, il faut lui indiquer de renvoyer
        un PUT*/

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}/delete", name="app_pins_delete", methods={"DELETE"})
     * @param Pin $pin
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function delete(Pin $pin, Request $request, EntityManagerInterface $em): Response
    {
        if($this->isCsrfTokenValid('pin_deletion_'.$pin->getId(),
            $request->get('crsf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully deleted');
        }

        return $this->redirectToRoute('app_home');
    }
}
