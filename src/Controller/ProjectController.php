<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;

use App\Document\Employee;
use App\Document\Address;
use App\Document\Project;
use App\Document\Manager;
use DateTime;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="project")
     */
    public function index(DocumentManager $dm)
    {
        $employee = new Employee();
        $employee->setName('Employee');
        $employee->setSalary(50000);
        $employee->setStarted(new DateTime());

        $address = new Address();
        $address->setAddress('555 Doctrine Rd.');
        $address->setCity('Nashville');
        $address->setState('TN');
        $address->setZipcode('37209');
        $employee->setAddress($address);

        $project = new Project('New Project');
        $manager = new Manager();
        $manager->setName('Manager');
        $manager->setSalary(100000);
        $manager->setStarted(new DateTime());
        $manager->addProject($project);

        $dm->persist($employee);
        $dm->persist($address);
        $dm->persist($project);
        $dm->persist($manager);
        $dm->flush();

        $employees = $dm->getRepository(Project::class)->findAll();

dump($employees);
        return $this->render('project/index.html.twig', [
            'employees' => $employees
        ]);
    }
}
