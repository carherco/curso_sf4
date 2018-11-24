<?php

namespace App\Controller;

use App\Entity\Alumno;
use App\Form\AlumnoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/alumno")
 */
class AlumnoController extends AbstractController
{
    /**
     * @Route("/cambiarnombre", name="alumno_cambiarnombre", methods="GET")
     */
    public function cambiarnombregrado(): Response
    {
        $alumno = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->find(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($alumno);
        
        $alumno->setNombre('Carlos');
        $em->detach($alumno);
        
        $em->flush();

        dump($alumno);
        return $this->render('alumno/index.html.twig', ['alumnos' => $alumnos]);
    }

    /**
     * @Route("/", name="alumno_index", methods="GET")
     */
    public function index(): Response
    {
        $alumnos = $this->getDoctrine()
            ->getRepository(Alumno::class)
            ->findAll();

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