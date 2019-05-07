<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Entity\Asignatura;
use App\Form\AlumnoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @Route("/alumno")
 */
class AlumnoController extends AbstractController
{
    /**
     * @Route("/inversedside", name="alumno_inversedside", methods="GET")
     */
    public function inversedside(): Response
    {
        $alumno = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->find(1);

        $asignatura = $this->getDoctrine()
            ->getRepository(Asignatura::class)
            ->find(3);

        $alumno->addAsignatura($asignatura);

        $em = $this->getDoctrine()->getManager();
        $em->persist($alumno);
        $em->flush();
return null;
        return $this->redirectToRoute('alumno_index');
    }

    /**
     * @Route("/ownerside", name="alumno_ownerside", methods="GET")
     */
    public function ownerside(): Response
    {
        $alumno = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->find(1);

        $asignatura = $this->getDoctrine()
            ->getRepository(Asignatura::class)
            ->find(3);

        $asignatura->addAlumno($alumno);

        $em = $this->getDoctrine()->getManager();
        $em->persist($asignatura);
        $em->flush();
        return null;
        return $this->redirectToRoute('alumno_index');
    }

    /**
     * @Route("/persist", name="alumno_persist", methods="GET")
     */
    public function persist(): Response
    {
        $alumno = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->find(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($alumno);
        $alumno->setNombre('Pedro');
        //$em->detach($alumno); 
        $em->flush();

        dump($alumno);
        return $this->render('alumno/index.html.twig', ['alumnos' => $alumnos]);
    }

    /**
     * @Route("/autocommitfalse", name="alumno_autocommitfalse", methods="GET")
     */
    public function autocommitfalse(): Response
    {
        $alumno = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->find(1);

        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $connection->setAutoCommit(false);

        $alumno->setNombre('Pedro');
        $em->persist($alumno);
        $em->flush();

        return $this->render('alumno/index.html.twig', ['alumnos' => $alumnos]);
    }

    /**
     * @Route("/", name="alumno_index", methods="GET")
     */
    public function index(EventDispatcherInterface $dispatcher): Response
    {
        $alumnos = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->findAll();

            
        $dispatcher->dispatch('mievento', new Event());
        
        return $this->render('alumno/index.html.twig', ['alumnos' => $alumnos]);
    }

    /**
     * @Route("/new", name="alumno_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $alumno = new Alumno();
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($alumno);
            $em->flush();

            return $this->redirectToRoute('alumno_index');
        }

        return $this->render('alumno/new.html.twig', [
            'alumno' => $alumno,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="alumno_show", methods="GET")
     */
    public function show(Alumno $alumno): Response
    {
        return $this->render('alumno/show.html.twig', ['alumno' => $alumno]);
    }

    /**
     * @Route("/{id}/edit", name="alumno_edit", methods="GET|POST")
     */
    public function edit(Request $request, Alumno $alumno): Response
    {
        $form = $this->createForm(AlumnoType::class, $alumno);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('alumno_edit', ['id' => $alumno->getId()]);
        }

        return $this->render('alumno/edit.html.twig', [
            'alumno' => $alumno,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="alumno_delete", methods="DELETE")
     */
    public function delete(Request $request, Alumno $alumno): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alumno->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($alumno);
            $em->flush();
        }

        return $this->redirectToRoute('alumno_index');
    }

}
